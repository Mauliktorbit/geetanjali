<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AbandonedCart;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKpis(): array
    {
        $now = now();

        return [
            'sales' => [
                'today' => $this->salesBetween($now->copy()->startOfDay(), $now->copy()->endOfDay()),
                'weekly' => $this->salesBetween($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
                'monthly' => $this->salesBetween($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
                'yearly' => $this->salesBetween($now->copy()->startOfYear(), $now->copy()->endOfYear()),
            ],
            'order_status_counts' => $this->orderStatusCounts(),
            'customers' => [
                'total' => Customer::count(),
                'new_today' => Customer::whereDate('created_at', $now->toDateString())->count(),
                'blocked' => Customer::where('is_blocked', true)->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->where('is_archived', false)->count(),
                'archived' => Product::where('is_archived', true)->count(),
            ],
            'inventory' => [
                'low_stock' => Inventory::whereColumn('available_stock', '<=', 'reorder_level')
                    ->where('available_stock', '>', 0)->count(),
                'out_of_stock' => Inventory::where('available_stock', '<=', 0)->count(),
            ],
            'pending_payments' => Order::whereIn('payment_status', [
                PaymentStatus::PENDING,
                PaymentStatus::PARTIALLY_PAID,
            ])->whereNotIn('status', [OrderStatus::CANCELLED])->count(),
            'refund_requests' => ReturnRequest::whereIn('status', [
                'requested', 'approved', 'inspected',
            ])->count(),
            'aov' => $this->averageOrderValue(),
            'best_sellers' => $this->bestSellers(),
            'recent_orders' => $this->recentOrders(),
            'revenue_by_payment_method' => $this->revenueByPaymentMethod(),
            'revenue_by_city' => $this->revenueByLocation('shipping_city'),
            'revenue_by_state' => $this->revenueByLocation('shipping_state'),
            'revenue_by_country' => $this->revenueByLocation('shipping_country'),
            'abandoned_carts' => [
                'count' => AbandonedCart::where('recovery_status', 'pending')->count(),
                'value' => (float) AbandonedCart::where('recovery_status', 'pending')->sum('cart_value'),
            ],
        ];
    }

    protected function salesBetween(Carbon $from, Carbon $to): float
    {
        return (float) Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->sum('grand_total');
    }

    protected function orderStatusCounts(): Collection
    {
        return Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    protected function averageOrderValue(): float
    {
        $query = Order::query()->whereNotIn('status', [OrderStatus::CANCELLED]);
        $count = (clone $query)->count();

        if ($count === 0) {
            return 0.0;
        }

        return round((float) $query->sum('grand_total') / $count, 2);
    }

    protected function bestSellers(int $limit = 10): Collection
    {
        return OrderItem::query()
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(total) as revenue'))
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', [OrderStatus::CANCELLED]))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get();
    }

    protected function recentOrders(int $limit = 10): Collection
    {
        return Order::query()
            ->with(['customer:id,name,email', 'items'])
            ->latest()
            ->limit($limit)
            ->get([
                'id', 'order_number', 'customer_id', 'customer_name', 'status',
                'payment_status', 'grand_total', 'created_at',
            ]);
    }

    protected function revenueByPaymentMethod(): Collection
    {
        return Payment::query()
            ->where('status', PaymentStatus::PAID)
            ->selectRaw('payment_method, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();
    }

    protected function revenueByLocation(string $column): Collection
    {
        return Order::query()
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("{$column} as location, SUM(grand_total) as revenue, COUNT(*) as orders")
            ->groupBy($column)
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();
    }
}
