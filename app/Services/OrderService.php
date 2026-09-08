<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InventoryService $inventoryService,
        protected NotificationService $notificationService
    ) {}

    public function createOrder(array $data, array $items): Order
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Order must contain at least one item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $order = Order::create(array_merge([
                'order_number' => $this->generateOrderNumber(),
                'status' => OrderStatus::NEW,
                'payment_status' => PaymentStatus::PENDING,
                'created_by' => Auth::id(),
                'currency' => $data['currency'] ?? 'INR',
            ], $data));

            foreach ($items as $item) {
                $this->createOrderItem($order, $item);
            }

            $this->recalculateTotals($order);

            if (! empty($order->warehouse_id)) {
                foreach ($order->items as $orderItem) {
                    if ($orderItem->product_id) {
                        $this->inventoryService->reserveStock(
                            $orderItem->product_id,
                            $orderItem->product_variant_id,
                            (int) $order->warehouse_id,
                            (int) $orderItem->quantity,
                            'Order #' . $order->order_number,
                            Order::class,
                            $order->id
                        );
                    }
                }
            }

            $this->recordStatusHistory($order, null, OrderStatus::NEW, 'Order created');
            $this->notificationService->notifyAdmins(
                'order_created',
                'New order ' . $order->order_number,
                'Order placed for ' . money($order->grand_total),
                '/admin/orders/' . $order->id,
                ['order_id' => $order->id]
            );

            return $order->fresh(['items', 'customer']);
        });
    }

    public function updateStatus(Order $order, string $toStatus, ?string $note = null): Order
    {
        if (! in_array($toStatus, OrderStatus::all(), true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        return DB::transaction(function () use ($order, $toStatus, $note) {
            $from = $order->status;

            if ($from === $toStatus) {
                return $order;
            }

            $updates = ['status' => $toStatus];

            if ($toStatus === OrderStatus::CONFIRMED) {
                $updates['confirmed_at'] = now();
            } elseif ($toStatus === OrderStatus::SHIPPED) {
                $updates['shipped_at'] = now();
            } elseif ($toStatus === OrderStatus::DELIVERED) {
                $updates['delivered_at'] = now();
                $this->fulfillReservedStock($order);
            } elseif ($toStatus === OrderStatus::CANCELLED) {
                $updates['cancelled_at'] = now();
                $updates['cancel_reason'] = $note;
                $this->releaseReservedStock($order);
            }

            $order->update($updates);
            $this->recordStatusHistory($order, $from, $toStatus, $note);
            $this->notificationService->notifyAdmins(
                'order_status',
                'Order ' . $order->order_number . ' → ' . OrderStatus::label($toStatus),
                $note,
                '/admin/orders/' . $order->id,
                ['order_id' => $order->id, 'status' => $toStatus]
            );

            return $order->fresh();
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if (! in_array($order->status, OrderStatus::cancellable(), true)) {
            throw new InvalidArgumentException('Order cannot be cancelled in current status.');
        }

        return $this->updateStatus($order, OrderStatus::CANCELLED, $reason ?? 'Cancelled by admin');
    }

    public function applyDiscount(Order $order, float $discountAmount, ?string $couponCode = null, ?int $couponId = null): Order
    {
        return DB::transaction(function () use ($order, $discountAmount, $couponCode, $couponId) {
            if ($discountAmount < 0) {
                throw new InvalidArgumentException('Discount cannot be negative.');
            }

            $order->update([
                'discount_amount' => min($discountAmount, (float) $order->subtotal),
                'coupon_code' => $couponCode,
                'coupon_id' => $couponId,
            ]);

            return $this->recalculateTotals($order);
        });
    }

    public function recalculateTotals(Order $order): Order
    {
        $order->loadMissing('items');

        $subtotal = 0.0;
        $tax = 0.0;
        $cost = 0.0;

        foreach ($order->items as $item) {
            $line = ((float) $item->unit_price * (int) $item->quantity) - (float) $item->discount;
            $subtotal += $line;
            $tax += (float) $item->tax;
            $cost += (float) $item->cost_price * (int) $item->quantity;

            $item->update([
                'total' => round($line + (float) $item->tax, 2),
            ]);
        }

        $discount = (float) $order->discount_amount;
        $shipping = (float) $order->shipping_charge;
        $cod = (float) $order->cod_charge;
        $grand = max(0, $subtotal - $discount + $tax + $shipping + $cod);

        $order->update([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($tax, 2),
            'cost_total' => round($cost, 2),
            'grand_total' => round($grand, 2),
        ]);

        return $order->fresh(['items']);
    }

    public function addItem(Order $order, array $itemData): OrderItem
    {
        return DB::transaction(function () use ($order, $itemData) {
            if (in_array($order->status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED], true)) {
                throw new InvalidArgumentException('Cannot modify items on this order.');
            }

            $item = $this->createOrderItem($order, $itemData);

            if ($order->warehouse_id && $item->product_id) {
                $this->inventoryService->reserveStock(
                    $item->product_id,
                    $item->product_variant_id,
                    (int) $order->warehouse_id,
                    (int) $item->quantity,
                    'Added to order #' . $order->order_number,
                    Order::class,
                    $order->id
                );
            }

            $this->recalculateTotals($order);

            return $item;
        });
    }

    public function removeItem(Order $order, OrderItem $item): Order
    {
        return DB::transaction(function () use ($order, $item) {
            if ($item->order_id !== $order->id) {
                throw new InvalidArgumentException('Item does not belong to this order.');
            }

            if ($order->warehouse_id && $item->product_id) {
                $this->inventoryService->releaseStock(
                    $item->product_id,
                    $item->product_variant_id,
                    (int) $order->warehouse_id,
                    (int) $item->quantity,
                    true,
                    'Removed from order #' . $order->order_number,
                    Order::class,
                    $order->id
                );
            }

            $item->delete();
            return $this->recalculateTotals($order);
        });
    }

    public function changeQuantity(Order $order, OrderItem $item, int $quantity): OrderItem
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        return DB::transaction(function () use ($order, $item, $quantity) {
            if ($item->order_id !== $order->id) {
                throw new InvalidArgumentException('Item does not belong to this order.');
            }

            $delta = $quantity - (int) $item->quantity;

            if ($delta !== 0 && $order->warehouse_id && $item->product_id) {
                if ($delta > 0) {
                    $this->inventoryService->reserveStock(
                        $item->product_id,
                        $item->product_variant_id,
                        (int) $order->warehouse_id,
                        $delta,
                        'Qty increase on #' . $order->order_number,
                        Order::class,
                        $order->id
                    );
                } else {
                    $this->inventoryService->releaseStock(
                        $item->product_id,
                        $item->product_variant_id,
                        (int) $order->warehouse_id,
                        abs($delta),
                        true,
                        'Qty decrease on #' . $order->order_number,
                        Order::class,
                        $order->id
                    );
                }
            }

            $line = ((float) $item->unit_price * $quantity) - (float) $item->discount;
            $tax = round($line * ((float) $item->tax_rate / 100), 2);

            $item->update([
                'quantity' => $quantity,
                'tax' => $tax,
                'total' => round($line + $tax, 2),
            ]);

            $this->recalculateTotals($order);

            return $item->fresh();
        });
    }

    protected function createOrderItem(Order $order, array $item): OrderItem
    {
        $product = ! empty($item['product_id']) ? Product::find($item['product_id']) : null;
        $variant = ! empty($item['product_variant_id']) ? ProductVariant::find($item['product_variant_id']) : null;

        $unitPrice = (float) ($item['unit_price']
            ?? $variant?->effective_price
            ?? $product?->effective_price
            ?? 0);
        $qty = (int) ($item['quantity'] ?? 1);
        $discount = (float) ($item['discount'] ?? 0);
        $taxRate = (float) ($item['tax_rate'] ?? $product?->taxRate?->igst ?? 0);
        $line = ($unitPrice * $qty) - $discount;
        $tax = round($line * ($taxRate / 100), 2);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product?->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $item['product_name'] ?? $product?->name ?? 'Item',
            'sku' => $item['sku'] ?? $variant?->sku ?? $product?->sku,
            'variant_label' => $item['variant_label'] ?? $variant?->name,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'cost_price' => $item['cost_price'] ?? $variant?->cost ?? $product?->cost_price ?? 0,
            'discount' => $discount,
            'tax' => $tax,
            'tax_rate' => $taxRate,
            'hsn_sac' => $item['hsn_sac'] ?? $product?->hsn_sac,
            'total' => round($line + $tax, 2),
            'meta' => $item['meta'] ?? null,
        ]);
    }

    protected function recordStatusHistory(Order $order, ?string $from, string $to, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'user_id' => Auth::id(),
        ]);
    }

    protected function releaseReservedStock(Order $order): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }
            $this->inventoryService->releaseStock(
                $item->product_id,
                $item->product_variant_id,
                (int) $order->warehouse_id,
                (int) $item->quantity,
                true,
                'Cancelled order #' . $order->order_number,
                Order::class,
                $order->id
            );
        }
    }

    protected function fulfillReservedStock(Order $order): void
    {
        if (! $order->warehouse_id) {
            return;
        }

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }
            $this->inventoryService->releaseStock(
                $item->product_id,
                $item->product_variant_id,
                (int) $order->warehouse_id,
                (int) $item->quantity,
                false,
                'Delivered order #' . $order->order_number,
                Order::class,
                $order->id
            );
        }
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
