<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Order;
use App\Repositories\CouponRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
     *   items?: list<array<string, mixed>>,
     *   product_ids?: array<int>,
     *   category_ids?: array<int>,
     *   collection_ids?: array<int>,
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
            if ($customer && $this->completedOrderCount($customer->id) > 0) {
                return $this->fail('This coupon is for a customer’s first order only.');
            }
        }

        if ($coupon->existing_customers_only) {
            if (! $customer) {
                return $this->fail('This coupon is for returning customers. Please sign in.');
            }

            if ($this->completedOrderCount($customer->id) === 0) {
                return $this->fail('This coupon is for customers who have already placed an order.');
            }
        }

        $perCustomer = (int) ($coupon->per_customer_limit ?? 0);
        if ($customer && $perCustomer > 0) {
            $used = Order::query()
                ->where('customer_id', $customer->id)
                ->where('coupon_id', $coupon->id)
                ->whereNotIn('status', [OrderStatus::CANCELLED])
                ->count();

            if ($used >= $perCustomer) {
                return $this->fail('You have already used this coupon the maximum number of times.');
            }
        }

        if (! empty($coupon->customer_groups)) {
            if (! $customer || ! in_array($customer->customer_group_id, $coupon->customer_groups, true)) {
                return $this->fail('Coupon is not available for your customer group.');
            }
        }

        $productIds = Coupon::normalizeIds($cart['product_ids'] ?? []);
        $categoryIds = Coupon::normalizeIds($cart['category_ids'] ?? []);
        $collectionIds = Coupon::normalizeIds($cart['collection_ids'] ?? []);
        $excludedProducts = $coupon->excludedProductIds();
        $includedProducts = $coupon->includedProductIds();
        $includedCategories = $coupon->includedCategoryIds();
        $includedCollections = $coupon->includedCollectionIds();
        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        if ($items !== []) {
            $eligible = $this->eligibleCartItems($coupon, $items);
            if ($eligible === []) {
                if ($coupon->exclude_sale_items && $this->allItemsOnSale($items)) {
                    return $this->fail('This coupon cannot be used on products that are already discounted.');
                }

                return $this->fail('This coupon does not apply to items in your cart.');
            }
            $discountBase = (float) collect($eligible)->sum(fn (array $item) => (float) ($item['line_total'] ?? 0));
        } else {
            if ($excludedProducts !== [] && array_intersect($productIds, $excludedProducts)) {
                return $this->fail('Cart contains products excluded from this coupon.');
            }

            if ($includedProducts !== [] && ! array_intersect($productIds, $includedProducts)) {
                return $this->fail('Coupon does not apply to products in the cart.');
            }

            if ($includedCategories !== [] && ! array_intersect($categoryIds, $includedCategories)) {
                return $this->fail('Coupon does not apply to categories in the cart.');
            }

            if ($includedCollections !== [] && ! array_intersect($collectionIds, $includedCollections)) {
                return $this->fail('Coupon does not apply to collections in the cart.');
            }

            $discountBase = $subtotal;
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

        $discount = $this->calculateDiscount($coupon, $discountBase);

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

    public function delete(Model $model): bool
    {
        if ($model instanceof Coupon) {
            Offer::query()->where('coupon_id', $model->id)->update(['coupon_id' => null]);
        }

        return parent::delete($model);
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
            'maximum_discount' => $data['maximum_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_customer_limit' => isset($data['per_customer_limit']) && $data['per_customer_limit'] !== null && $data['per_customer_limit'] !== ''
                ? (int) $data['per_customer_limit']
                : 0,
            'new_customers_only' => (bool) ($data['new_customers_only'] ?? false),
            'existing_customers_only' => (bool) ($data['existing_customers_only'] ?? false),
            'exclude_sale_items' => (bool) ($data['exclude_sale_items'] ?? false),
            'included_categories' => $this->nullableIdList($data['included_categories'] ?? null),
            'included_collections' => $this->nullableIdList($data['included_collections'] ?? null),
            'included_products' => $this->nullableIdList($data['included_products'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($coupon) {
            return $this->update($coupon, $payload);
        }

        $payload['is_stackable'] = false;
        $payload['usage_count'] = 0;

        return $this->create($payload);
    }

    private function completedOrderCount(int $customerId): int
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->count();
    }

    /**
     * @param  mixed  $value
     * @return list<int>|null
     */
    private function nullableIdList(mixed $value): ?array
    {
        $ids = Coupon::normalizeIds($value);

        return $ids === [] ? null : $ids;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function eligibleCartItems(Coupon $coupon, array $items): array
    {
        $excludedProducts = $coupon->excludedProductIds();
        $includedProducts = $coupon->includedProductIds();
        $includedCategories = $coupon->includedCategoryIds();
        $includedCollections = $coupon->includedCollectionIds();
        $hasScope = $includedProducts !== [] || $includedCategories !== [] || $includedCollections !== [];

        $eligible = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['id'] ?? 0);
            if ($productId > 0 && in_array($productId, $excludedProducts, true)) {
                continue;
            }

            if ($coupon->exclude_sale_items && $this->itemIsOnSale($item)) {
                continue;
            }

            if ($hasScope && ! $this->itemMatchesScope($item, $includedProducts, $includedCategories, $includedCollections)) {
                continue;
            }

            $eligible[] = $item;
        }

        return $eligible;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function allItemsOnSale(array $items): bool
    {
        $catalogItems = array_values(array_filter($items, 'is_array'));
        if ($catalogItems === []) {
            return false;
        }

        foreach ($catalogItems as $item) {
            if (! $this->itemIsOnSale($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function itemIsOnSale(array $item): bool
    {
        if (array_key_exists('on_sale', $item)) {
            return (bool) $item['on_sale'];
        }

        $price = (float) ($item['price'] ?? 0);
        $compare = (float) ($item['compare_at_price'] ?? 0);

        return $compare > $price && $price > 0;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<int>  $productIds
     * @param  list<int>  $categoryIds
     * @param  list<int>  $collectionIds
     */
    private function itemMatchesScope(array $item, array $productIds, array $categoryIds, array $collectionIds): bool
    {
        $productId = (int) ($item['id'] ?? 0);
        if ($productIds !== [] && in_array($productId, $productIds, true)) {
            return true;
        }

        $itemCategories = Coupon::normalizeIds([
            $item['category_id'] ?? 0,
            $item['subcategory_id'] ?? 0,
        ]);
        if ($categoryIds !== [] && array_intersect($itemCategories, $categoryIds)) {
            return true;
        }

        $itemCollections = Coupon::normalizeIds($item['collection_ids'] ?? []);
        if ($collectionIds !== [] && array_intersect($itemCollections, $collectionIds)) {
            return true;
        }

        return false;
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
