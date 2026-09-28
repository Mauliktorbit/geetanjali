<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public const SHIPPING = [
        'standard' => [
            'label' => 'Standard Delivery',
            'charge' => 0.0,
            'eta' => '3–5 business days',
            'days' => 5,
        ],
        'express' => [
            'label' => 'Express Delivery',
            'charge' => 199.0,
            'eta' => '1–2 business days',
            'days' => 2,
        ],
    ];

    public const PAYMENTS = [
        'upi' => 'UPI',
        'card' => 'Credit / Debit Card',
        'netbanking' => 'Net Banking',
        'cod' => 'Cash on Delivery',
        'wallet' => 'Wallet',
    ];

    public function __construct(
        private readonly CartService $cart,
        private readonly NotificationService $notifications,
        private readonly PaymentService $payments,
        private readonly ShippingMethodService $shippingMethods,
        private readonly CouponService $coupons,
        private readonly CustomerMailService $customerMail,
    ) {}

    /**
     * @return array<string, array{label: string, charge: float, eta: string, days: int, free_above: float|null}>
     */
    public function shippingOptions(?float $orderAmount = null): array
    {
        return $this->shippingMethods->checkoutOptions($orderAmount);
    }

    /**
     * @return array{label: string, eta: string, days: int}
     */
    public function shippingMeta(string $code): array
    {
        return $this->shippingMethods->metaForCode($code);
    }

    public function ensureCustomer(User $user): Customer
    {
        return Customer::forUser($user, true);
    }

    /**
     * @return list<CustomerAddress>
     */
    public function addresses(Customer $customer): array
    {
        return $customer->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->all();
    }

    public function findAddress(Customer $customer, int $id): ?CustomerAddress
    {
        return $customer->addresses()->whereKey($id)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveAddress(Customer $customer, array $data, ?CustomerAddress $address = null): CustomerAddress
    {
        $payload = [
            'type' => 'shipping',
            'label' => $data['label'] ?? 'home',
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => 'India',
            'pincode' => $data['pincode'],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];

        if ($address) {
            $address->update($payload);
        } else {
            $address = $customer->addresses()->create($payload);
        }

        if ($address->is_default) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return $address->fresh();
    }

    /**
     * @param  array{address_id: int, shipping_method: string, payment_method: string}  $input
     */
    public function placeOrder(User $user, Customer $customer, array $input): Order
    {
        $summary = $this->cart->summary();
        $address = $this->findAddress($customer, (int) $input['address_id']);

        if (! $address) {
            throw new \InvalidArgumentException('Please select a delivery address.');
        }

        $orderAmount = max(0, (float) $summary['subtotal'] - (float) $summary['discount']);
        $shippingOptions = $this->shippingOptions($orderAmount);
        $shippingKey = $input['shipping_method'] ?? array_key_first($shippingOptions);
        $paymentKey = $input['payment_method'] ?? 'upi';

        if (! $shippingKey || ! isset($shippingOptions[$shippingKey])) {
            throw new \InvalidArgumentException('Please choose a delivery method.');
        }

        if (! isset(self::PAYMENTS[$paymentKey])) {
            throw new \InvalidArgumentException('Please choose a payment method.');
        }

        $shipping = $shippingOptions[$shippingKey];
        $shippingCharge = (float) $shipping['charge'];
        $grandTotal = max(0, $orderAmount + $shippingCharge);
        $isCod = $paymentKey === 'cod';
        $couponModel = null;
        if (! empty($summary['coupon'])) {
            $couponModel = Coupon::query()
                ->whereRaw('UPPER(code) = ?', [strtoupper((string) $summary['coupon'])])
                ->first();
        }

        $shippingPayload = [
            'label' => $address->label,
            'name' => $address->name,
            'phone' => $address->phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'pincode' => $address->pincode,
            'shipping_method_code' => $shippingKey,
            'shipping_method_label' => (string) ($shipping['label'] ?? ''),
            'shipping_eta' => (string) ($shipping['eta'] ?? ''),
        ];

        $order = DB::transaction(function () use (
            $user,
            $customer,
            $summary,
            $address,
            $shipping,
            $shippingKey,
            $paymentKey,
            $shippingCharge,
            $grandTotal,
            $isCod,
            $shippingPayload,
            $couponModel
        ) {
            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_id' => $customer->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->mobile ?: $user->phone,
                'source' => 'website',
                'sales_channel' => 'online',
                'status' => OrderStatus::CONFIRMED,
                'payment_status' => $isCod ? 'unpaid' : 'paid',
                'payment_method' => $paymentKey,
                'shipping_method' => $shippingKey,
                'coupon_id' => $couponModel?->id,
                'coupon_code' => $summary['coupon'],
                'subtotal' => $summary['subtotal'],
                'discount_amount' => $summary['discount'],
                'tax_amount' => 0,
                'shipping_charge' => $shippingCharge,
                'cod_charge' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $isCod ? 0 : $grandTotal,
                'currency' => 'INR',
                'billing_address' => $shippingPayload,
                'shipping_address' => $shippingPayload,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_country' => $address->country,
                'shipping_pincode' => $address->pincode,
                'customer_notes' => $summary['gift_message'],
                'confirmed_at' => now(),
            ]);

            foreach ($summary['items'] as $item) {
                $productId = Product::query()->whereKey($item['id'] ?? 0)->exists()
                    ? (int) $item['id']
                    : null;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $item['name'],
                    'sku' => $item['slug'] ?? null,
                    'variant_label' => $item['metal'] ?? null,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $item['line_total'],
                    'meta' => [
                        'image' => $item['image'] ?? null,
                        'weight' => $item['weight'] ?? null,
                    ],
                ]);
            }

            $customer->update([
                'total_orders' => (int) $customer->total_orders + 1,
                'total_spent' => (float) $customer->total_spent + $grandTotal,
                'last_order_at' => now(),
                'average_order_value' => ((float) $customer->total_spent + $grandTotal) / max(1, (int) $customer->total_orders + 1),
            ]);

            $this->cart->clear();

            if ($couponModel) {
                $this->coupons->incrementUsage($couponModel);
            }

            $this->payments->createFromOrder($order);

            $this->notifications->notifyNewOrder($order->load('items'));

            return $order;
        });

        $this->customerMail->sendOrderConfirmed($order->load('items'));

        return $order;
    }

    public function expectedDelivery(Order $order): \Carbon\CarbonInterface
    {
        $days = $this->shippingMethods->metaForCode((string) $order->shipping_method)['days'];

        return now()->addWeekdays($days)->startOfDay();
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'GJ'.now()->format('ymd').strtoupper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
