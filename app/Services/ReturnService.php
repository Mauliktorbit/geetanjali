<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Repositories\ReturnRequestRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnService
{
    public function __construct(
        protected ReturnRequestRepository $returnRepository,
        protected InventoryService $inventoryService,
        protected PaymentService $paymentService,
        protected OrderService $orderService,
        protected NotificationService $notificationService
    ) {}

    public function requestReturn(Order $order, array $items, array $data = []): ReturnRequest
    {
        if ($order->status !== OrderStatus::DELIVERED && $order->status !== OrderStatus::RETURN_REQUESTED) {
            throw new InvalidArgumentException('Returns are only allowed for delivered orders.');
        }

        if (empty($items)) {
            throw new InvalidArgumentException('At least one return item is required.');
        }

        return DB::transaction(function () use ($order, $items, $data) {
            $refundAmount = 0.0;
            $prepared = [];

            foreach ($items as $row) {
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->findOrFail($row['order_item_id']);
                $qty = min((int) ($row['quantity'] ?? 1), (int) $orderItem->quantity);
                $lineRefund = round(((float) $orderItem->total / max(1, (int) $orderItem->quantity)) * $qty, 2);
                $refundAmount += $lineRefund;
                $prepared[] = [
                    'order_item_id' => $orderItem->id,
                    'quantity' => $qty,
                    'refund_amount' => $lineRefund,
                ];
            }

            $return = ReturnRequest::create([
                'return_number' => $this->generateReturnNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_reason_id' => $data['return_reason_id'] ?? null,
                'type' => $data['type'] ?? 'return',
                'status' => 'requested',
                'customer_reason' => $data['customer_reason'] ?? null,
                'evidence_files' => $data['evidence_files'] ?? null,
                'refund_amount' => $refundAmount,
                'return_shipping_deduction' => $data['return_shipping_deduction'] ?? 0,
                'refund_method' => $data['refund_method'] ?? 'original',
                'restock' => $data['restock'] ?? true,
            ]);

            foreach ($prepared as $row) {
                ReturnItem::create(array_merge($row, ['return_id' => $return->id]));
            }

            $this->orderService->updateStatus($order, OrderStatus::RETURN_REQUESTED, 'Return requested');

            $this->notificationService->notifyAdmins(
                'return_requested',
                'Return ' . $return->return_number,
                'Return requested for order ' . $order->order_number,
                '/admin/returns/' . $return->id,
                ['return_id' => $return->id]
            );

            return $return->fresh(['items']);
        });
    }

    public function approve(ReturnRequest $return, ?string $note = null): ReturnRequest
    {
        return DB::transaction(function () use ($return, $note) {
            if ($return->status !== 'requested') {
                throw new InvalidArgumentException('Only requested returns can be approved.');
            }

            $return->update([
                'status' => 'approved',
                'approved_at' => now(),
                'reviewed_by' => Auth::id(),
                'inspection_notes' => $note ?? $return->inspection_notes,
            ]);

            return $return->fresh();
        });
    }

    public function reject(ReturnRequest $return, string $reason): ReturnRequest
    {
        return DB::transaction(function () use ($return, $reason) {
            if (! in_array($return->status, ['requested', 'approved'], true)) {
                throw new InvalidArgumentException('Return cannot be rejected in current status.');
            }

            $return->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'reviewed_by' => Auth::id(),
                'inspection_notes' => $reason,
            ]);

            if ($return->order) {
                $this->orderService->updateStatus($return->order, OrderStatus::DELIVERED, 'Return rejected');
            }

            return $return->fresh();
        });
    }

    public function completeInspection(ReturnRequest $return, string $inspectionStatus, ?string $notes = null): ReturnRequest
    {
        return DB::transaction(function () use ($return, $inspectionStatus, $notes) {
            if (! in_array($return->status, ['approved', 'picked_up'], true)) {
                throw new InvalidArgumentException('Inspect only approved or picked-up returns.');
            }

            $return->update([
                'status' => 'inspected',
                'inspection_status' => $inspectionStatus,
                'inspection_notes' => $notes ?? $return->inspection_notes,
            ]);

            if ($return->restock && $inspectionStatus === 'passed' && $return->order?->warehouse_id) {
                foreach ($return->items as $item) {
                    $orderItem = $item->orderItem;
                    if (! $orderItem?->product_id) {
                        continue;
                    }
                    $this->inventoryService->adjustStock(
                        $orderItem->product_id,
                        $orderItem->product_variant_id,
                        (int) $return->order->warehouse_id,
                        (int) $item->quantity,
                        'Restock from return ' . $return->return_number,
                        'return',
                        ReturnRequest::class,
                        $return->id
                    );
                }
            }

            return $return->fresh(['items']);
        });
    }

    public function processRefund(ReturnRequest $return): ReturnRequest
    {
        return DB::transaction(function () use ($return) {
            if (! in_array($return->status, ['approved', 'inspected', 'picked_up'], true)) {
                throw new InvalidArgumentException('Return is not ready for refund.');
            }

            $amount = max(0, (float) $return->refund_amount - (float) $return->return_shipping_deduction);

            $this->paymentService->processRefund(
                $return->order,
                $amount,
                null,
                $return->refund_method ?? 'original',
                'Refund for return ' . $return->return_number,
                $return->id
            );

            $return->update([
                'status' => 'refunded',
                'completed_at' => now(),
            ]);

            $this->orderService->updateStatus(
                $return->order,
                OrderStatus::REFUNDED,
                'Refunded via return ' . $return->return_number
            );

            return $return->fresh();
        });
    }

    public function createReplacement(ReturnRequest $return): Order
    {
        return DB::transaction(function () use ($return) {
            if ($return->type !== 'replacement' && $return->type !== 'return') {
                throw new InvalidArgumentException('Invalid return type for replacement.');
            }

            $order = $return->order()->with('items')->firstOrFail();
            $items = [];

            foreach ($return->items as $item) {
                $orderItem = $item->orderItem;
                $items[] = [
                    'product_id' => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'product_name' => $orderItem->product_name,
                    'sku' => $orderItem->sku,
                    'variant_label' => $orderItem->variant_label,
                    'quantity' => $item->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'cost_price' => $orderItem->cost_price,
                    'tax_rate' => $orderItem->tax_rate,
                    'hsn_sac' => $orderItem->hsn_sac,
                ];
            }

            $replacement = $this->orderService->createOrder([
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'source' => 'replacement',
                'billing_address' => $order->billing_address,
                'shipping_address' => $order->shipping_address,
                'shipping_city' => $order->shipping_city,
                'shipping_state' => $order->shipping_state,
                'shipping_country' => $order->shipping_country,
                'shipping_pincode' => $order->shipping_pincode,
                'warehouse_id' => $order->warehouse_id,
                'payment_method' => 'replacement',
                'payment_status' => 'paid',
                'internal_notes' => 'Replacement for return ' . $return->return_number,
            ], $items);

            $return->update([
                'replacement_order_id' => $replacement->id,
                'status' => 'replacement_created',
                'completed_at' => now(),
            ]);

            return $replacement;
        });
    }

    protected function generateReturnNumber(): string
    {
        do {
            $number = 'RET-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (ReturnRequest::where('return_number', $number)->exists());

        return $number;
    }
}
