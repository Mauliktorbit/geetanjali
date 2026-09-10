<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerPaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class AccountService
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly WishlistService $wishlist,
    ) {}

    public function ensureCustomer(User $user): Customer
    {
        return $this->checkout->ensureCustomer($user)->loadMissing('group');
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $customer = $this->ensureCustomer($user);
        $addresses = $this->checkout->addresses($customer);
        $recentOrders = $customer->orders()
            ->with('items')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Order $order) => $this->presentOrder($order));

        return [
            'customer' => $customer,
            'addresses' => $addresses,
            'recentOrders' => $recentOrders,
            'stats' => [
                'orders' => (int) $customer->orders()->count(),
                'wishlist' => $this->wishlist->count(),
                'coupons' => $this->activeCoupons()->count(),
                'rewards' => (int) $customer->reward_points,
            ],
            'paymentMethods' => $customer->paymentMethods()->orderByDesc('is_default')->orderBy('id')->get(),
            'notifications' => $customer->notificationPrefs(),
        ];
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function activeCoupons(): Collection
    {
        $now = now();

        return Coupon::query()
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function savePaymentMethod(Customer $customer, array $data): CustomerPaymentMethod
    {
        $makeDefault = (bool) ($data['is_default'] ?? false) || $customer->paymentMethods()->doesntExist();

        $method = $customer->paymentMethods()->create([
            'brand' => $data['brand'],
            'last_four' => $data['last_four'],
            'holder_name' => $data['holder_name'] ?? $customer->name,
            'expiry_month' => $data['expiry_month'] ?? null,
            'expiry_year' => $data['expiry_year'] ?? null,
            'is_default' => $makeDefault,
        ]);

        if ($method->is_default) {
            $customer->paymentMethods()->where('id', '!=', $method->id)->update(['is_default' => false]);
        }

        return $method;
    }

    public function orderStatusTone(string $status): string
    {
        return match ($status) {
            OrderStatus::DELIVERED => 'delivered',
            OrderStatus::SHIPPED, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::READY_TO_SHIP => 'shipped',
            OrderStatus::CANCELLED, OrderStatus::FAILED_DELIVERY, OrderStatus::RTO => 'cancelled',
            OrderStatus::RETURNED, OrderStatus::REFUNDED, OrderStatus::PARTIALLY_REFUNDED, OrderStatus::RETURN_REQUESTED => 'return',
            default => 'processing',
        };
    }

    public function orderThumb(Order $order): ?string
    {
        $item = $order->items->first();
        $image = $item?->meta['image'] ?? null;

        return is_string($image) && $image !== '' ? $image : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function presentOrder(Order $order): array
    {
        if (! $order->relationLoaded('items')) {
            $order->load('items');
        }

        return [
            'number' => $order->order_number,
            'date' => optional($order->created_at)->format('d M Y'),
            'items' => (int) $order->items->sum('quantity'),
            'total' => (float) $order->grand_total,
            'status' => $order->status,
            'status_label' => OrderStatus::customerLabel((string) $order->status),
            'tone' => $this->orderStatusTone((string) $order->status),
            'thumb' => $this->orderThumb($order),
            'name' => (($summary = $order->productSummary()) === '—' ? '' : $summary),
        ];
    }
}
