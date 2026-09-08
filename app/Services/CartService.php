<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    private const COUPON_KEY = 'cart_coupon';

    private const GIFT_KEY = 'cart_gift_message';

    public function __construct(
        private readonly CatalogService $catalog,
        private readonly CouponService $coupons,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, count: int, subtotal: float, discount: float, shipping: float, total: float, savings: float, coupon: string|null, gift_message: string|null}
     */
    public function summary(): array
    {
        $items = $this->items();
        $subtotal = (float) collect($items)->sum(fn (array $item) => $item['line_total']);
        $compareTotal = (float) collect($items)->sum(fn (array $item) => ($item['compare_at_price'] ?? $item['price']) * $item['quantity']);
        $itemSavings = max(0, $compareTotal - $subtotal);

        $coupon = $this->couponState();
        $couponDiscount = 0.0;

        if (is_array($coupon) && isset($coupon['type'], $coupon['value'])) {
            $couponDiscount = in_array($coupon['type'], ['percent', 'percentage'], true)
                ? round($subtotal * ((float) $coupon['value'] / 100), 2)
                : min($subtotal, (float) $coupon['value']);
        }

        $shipping = 0.0;
        $total = max(0, $subtotal - $couponDiscount + $shipping);

        return [
            'items' => $items,
            'count' => (int) collect($items)->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $couponDiscount,
            'shipping' => $shipping,
            'total' => $total,
            'savings' => $itemSavings + $couponDiscount,
            'coupon' => is_array($coupon) ? ($coupon['code'] ?? null) : null,
            'gift_message' => $this->giftMessage(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(): array
    {
        $customer = $this->customer();
        $raw = $customer ? $this->databaseRows($customer) : $this->sessionRows();

        return array_values(array_filter(array_map(function (array $row) {
            $product = $this->catalog->present((int) ($row['id'] ?? 0), $row['payload'] ?? $row);
            if ($product === null) {
                return null;
            }

            $qty = max(1, (int) ($row['quantity'] ?? 1));
            $price = (float) $product['price'];

            return array_merge($product, [
                'quantity' => $qty,
                'line_total' => $price * $qty,
            ]);
        }, $raw)));
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum('quantity');
    }

    public function add(int $productId, int $quantity = 1, array $snapshot = []): bool
    {
        $product = $this->catalog->resolve($productId, $snapshot);
        if ($product === null) {
            return false;
        }

        $qty = max(1, min(10, $quantity));
        $customer = $this->customer(true);

        if ($customer) {
            $item = CartItem::query()->firstOrNew([
                'customer_id' => $customer->id,
                'product_id' => $productId,
            ]);
            $item->quantity = min(10, (int) $item->quantity + $qty);
            $item->payload = $product;
            $item->save();
            $this->forgetSessionCart();

            return true;
        }

        $cart = $this->sessionMap();
        $key = (string) $productId;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = min(10, ((int) $cart[$key]['quantity']) + $qty);
        } else {
            $cart[$key] = array_merge($product, ['quantity' => $qty]);
        }
        Session::put(self::SESSION_KEY, $cart);

        return true;
    }

    public function update(int $productId, int $quantity): void
    {
        $customer = $this->customer();

        if ($customer) {
            $item = CartItem::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->first();

            if (! $item) {
                return;
            }

            if ($quantity < 1) {
                $item->delete();
            } else {
                $item->quantity = min(10, $quantity);
                $item->save();
            }

            return;
        }

        $cart = $this->sessionMap();
        $key = (string) $productId;
        if (! isset($cart[$key])) {
            return;
        }
        if ($quantity < 1) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = min(10, $quantity);
        }
        Session::put(self::SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $customer = $this->customer();

        if ($customer) {
            CartItem::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->delete();

            return;
        }

        $cart = $this->sessionMap();
        unset($cart[(string) $productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function applyCoupon(string $code): array
    {
        $code = strtoupper(trim($code));
        $items = $this->items();
        $subtotal = (float) collect($items)->sum(fn (array $item) => $item['line_total']);
        $customer = $this->customer();

        $result = $this->coupons->validate($code, [
            'subtotal' => $subtotal,
            'product_ids' => collect($items)->pluck('id')->all(),
            'customer_id' => $customer?->id,
        ]);

        if ($result['valid'] && isset($result['coupon'])) {
            $coupon = $result['coupon'];
            $state = [
                'code' => $coupon->code,
                'type' => $coupon->discount_type,
                'value' => (float) $coupon->discount_value,
            ];
            $this->storeCoupon($state);

            return ['success' => true, 'message' => $result['message'], 'code' => $state['code']];
        }

        $legacy = [
            'GEET10' => ['type' => 'percent', 'value' => 10],
            'GOLD15' => ['type' => 'percent', 'value' => 15],
            'PREPAID10' => ['type' => 'percent', 'value' => 10],
            'FLAT5000' => ['type' => 'flat', 'value' => 5000],
        ];

        if (! isset($legacy[$code])) {
            $this->clearCoupon();

            return ['success' => false, 'message' => $result['message'] ?? 'Invalid or expired coupon code.'];
        }

        $state = array_merge($legacy[$code], ['code' => $code]);
        $this->storeCoupon($state);

        return ['success' => true, 'message' => 'Coupon applied successfully.', 'code' => $code];
    }

    public function clearCoupon(): void
    {
        Session::forget(self::COUPON_KEY);
        $this->customerCart()?->update(['coupon_code' => null]);
    }

    public function setGiftMessage(?string $message): void
    {
        $message = trim((string) $message);
        if ($message === '') {
            Session::forget(self::GIFT_KEY);
            $this->customerCart()?->update(['gift_message' => null]);

            return;
        }

        $value = mb_substr($message, 0, 500);
        $customer = $this->customer(true);
        if ($customer) {
            $this->ensureCartRow($customer)->update(['gift_message' => $value]);
            Session::forget(self::GIFT_KEY);

            return;
        }

        Session::put(self::GIFT_KEY, $value);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget(self::COUPON_KEY);
        Session::forget(self::GIFT_KEY);

        $customer = $this->customer();
        if ($customer) {
            CartItem::query()->where('customer_id', $customer->id)->delete();
            $this->customerCart()?->update([
                'coupon_code' => null,
                'gift_message' => null,
            ]);
        }
    }

    /**
     * @param  mixed  $guestCart
     * @param  mixed  $guestCoupon
     */
    public function importGuest(mixed $guestCart, mixed $guestCoupon = null, mixed $guestGift = null): void
    {
        $customer = $this->customer(true);
        if (! $customer) {
            return;
        }

        if (is_array($guestCart)) {
            foreach ($guestCart as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $id = (int) ($row['id'] ?? 0);
                $qty = max(1, (int) ($row['quantity'] ?? 1));
                if ($id > 0) {
                    $this->add($id, $qty, $row);
                }
            }
        }

        if (is_array($guestCoupon) && ! empty($guestCoupon['code'])) {
            $this->applyCoupon((string) $guestCoupon['code']);
        }

        if (is_string($guestGift) && trim($guestGift) !== '') {
            $this->setGiftMessage($guestGift);
        }

        $this->forgetSessionCart();
    }

    private function customer(bool $create = false): ?Customer
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user || $user->is_staff) {
            return null;
        }

        if ($user->customer) {
            return $user->customer;
        }

        if (! $create) {
            return null;
        }

        return Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->mobile ?: $user->phone,
            'is_verified' => true,
            'acquisition_source' => 'website',
        ]);
    }

    private function customerCart(): ?Cart
    {
        $customer = $this->customer();

        return $customer
            ? Cart::query()->where('customer_id', $customer->id)->first()
            : null;
    }

    private function ensureCartRow(Customer $customer): Cart
    {
        return Cart::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['coupon_code' => null, 'gift_message' => null]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function databaseRows(Customer $customer): array
    {
        return CartItem::query()
            ->where('customer_id', $customer->id)
            ->orderBy('id')
            ->get()
            ->map(fn (CartItem $item) => [
                'id' => (int) $item->product_id,
                'quantity' => (int) $item->quantity,
                'payload' => $item->payload,
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sessionRows(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);

        return array_values(is_array($raw) ? $raw : []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function sessionMap(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);

        return is_array($raw) ? $raw : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function couponState(): ?array
    {
        $session = Session::get(self::COUPON_KEY);
        if (is_array($session) && ! empty($session['code'])) {
            return $session;
        }

        $code = $this->customerCart()?->coupon_code;
        if (! $code) {
            return null;
        }

        $items = $this->items();
        $subtotal = (float) collect($items)->sum(fn (array $item) => $item['line_total']);
        $check = $this->coupons->validate($code, [
            'subtotal' => $subtotal,
            'product_ids' => collect($items)->pluck('id')->all(),
            'customer_id' => $this->customer()?->id,
        ]);

        if ($check['valid'] && isset($check['coupon'])) {
            return [
                'code' => $check['coupon']->code,
                'type' => $check['coupon']->discount_type,
                'value' => (float) $check['coupon']->discount_value,
            ];
        }

        return ['code' => $code, 'type' => 'percent', 'value' => 0];
    }

    /**
     * @param  array{code: string, type: string, value: float}  $state
     */
    private function storeCoupon(array $state): void
    {
        Session::put(self::COUPON_KEY, $state);
        $customer = $this->customer(true);
        if ($customer) {
            $this->ensureCartRow($customer)->update(['coupon_code' => $state['code']]);
        }
    }

    private function giftMessage(): ?string
    {
        $customer = $this->customer();
        if ($customer) {
            return $this->customerCart()?->gift_message ?: Session::get(self::GIFT_KEY);
        }

        $value = Session::get(self::GIFT_KEY);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function forgetSessionCart(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
