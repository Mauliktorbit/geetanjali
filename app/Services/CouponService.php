<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\CouponRepository;
use Carbon\Carbon;

class CouponService extends BaseService
{
    public function __construct(CouponRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Validate a coupon against cart context.
     *
     * @param  array{
     *   subtotal: float,
     *   product_ids?: array<int>,
     *   category_ids?: array<int>,
     *   customer_id?: int|null,
     *   payment_method?: string|null,
     *   location?: array{city?: string, state?: string, country?: string, pincode?: string}|null
     * }  $cart
     * @return array{valid: bool, coupon?: Coupon, discount: float, message: string}
     */
    public function validate(string $code, array $cart): array
    {
        $code = trim($code);
        $coupon = Coupon::query()
            ->where(function ($q) use ($code) {
                $q->where('code', $code)->orWhere('code', strtoupper($code));
            })
            ->first();

        if (! $coupon || ! $coupon->is_active) {
            return $this->fail('Invalid or inactive coupon code.');
        }

        $now = Carbon::now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return $this->fail('Coupon is not active yet.');
        }

        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            return $this->fail('Coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            return $this->fail('Coupon usage limit reached.');
        }

        $subtotal = (float) ($cart['subtotal'] ?? 0);
        if ($coupon->minimum_cart !== null && $subtotal < (float) $coupon->minimum_cart) {
            return $this->fail('Minimum cart value of ' . money($coupon->minimum_cart) . ' required.');
        }

        $customer = ! empty($cart['customer_id']) ? Customer::find($cart['customer_id']) : null;

        if ($coupon->new_customers_only) {
            if (! $customer || (int) $customer->total_orders > 0) {
                return $this->fail('Coupon is valid for new customers only.');
            }
        }

        if ($customer && $coupon->per_customer_limit) {
            $used = Order::where('customer_id', $customer->id)
                ->where('coupon_id', $coupon->id)
                ->whereNotIn('status', ['cancelled'])
                ->count();

            if ($used >= (int) $coupon->per_customer_limit) {
                return $this->fail('You have already used this coupon the maximum number of times.');
            }
        }

        if (! empty($coupon->customer_groups)) {
            if (! $customer || ! in_array($customer->customer_group_id, $coupon->customer_groups, true)) {
                return $this->fail('Coupon is not available for your customer group.');
            }
        }

        $productIds = $cart['product_ids'] ?? [];
        $categoryIds = $cart['category_ids'] ?? [];

        if (! empty($coupon->excluded_products) && array_intersect($productIds, $coupon->excluded_products)) {
            return $this->fail('Cart contains products excluded from this coupon.');
        }

        if (! empty($coupon->included_products) && ! array_intersect($productIds, $coupon->included_products)) {
            return $this->fail('Coupon does not apply to products in the cart.');
        }

        if (! empty($coupon->included_categories) && ! array_intersect($categoryIds, $coupon->included_categories)) {
            return $this->fail('Coupon does not apply to categories in the cart.');
        }

        if (! empty($coupon->payment_methods) && ! empty($cart['payment_method'])) {
            if (! in_array($cart['payment_method'], $coupon->payment_methods, true)) {
                return $this->fail('Coupon is not valid for the selected payment method.');
            }
        }

        if (! empty($coupon->locations) && ! empty($cart['location'])) {
            $location = $cart['location'];
            $matched = false;
            foreach ($coupon->locations as $rule) {
                foreach (['pincode', 'city', 'state', 'country'] as $field) {
                    if (! empty($rule[$field]) && ! empty($location[$field])
                        && strcasecmp((string) $rule[$field], (string) $location[$field]) === 0) {
                        $matched = true;
                        break 2;
                    }
                }
            }
            if (! $matched) {
                return $this->fail('Coupon is not valid for your location.');
            }
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon applied successfully.',
        ];
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = match ($coupon->discount_type) {
            'percentage', 'percent' => round($subtotal * ((float) $coupon->discount_value / 100), 2),
            'fixed', 'flat' => (float) $coupon->discount_value,
            'free_shipping' => 0.0,
            default => (float) $coupon->discount_value,
        };

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        return min($discount, $subtotal);
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('usage_count');
    }

    public function save(array $data, ?Coupon $coupon = null): Coupon
    {
        $payload = [
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => trim((string) $data['name']),
            'discount_type' => ($data['discount_type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent',
            'discount_value' => $data['discount_value'],
            'starts_at' => parse_dmy($data['starts_at'] ?? null),
            'ends_at' => parse_dmy($data['ends_at'] ?? null, true),
            'minimum_cart' => $data['minimum_cart'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($coupon) {
            return $this->update($coupon, $payload);
        }

        $payload['new_customers_only'] = false;
        $payload['is_stackable'] = false;
        $payload['usage_count'] = 0;

        return $this->create($payload);
    }

    protected function fail(string $message): array
    {
        return [
            'valid' => false,
            'discount' => 0.0,
            'message' => $message,
        ];
    }
}
