<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Enquiry;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function overview(): array
    {
        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $openStatuses = [
            OrderStatus::NEW,
            OrderStatus::ON_HOLD,
            OrderStatus::CONFIRMED,
        ];

        $paidOrders = fn () => Order::query()->whereNotIn('status', [OrderStatus::CANCELLED]);

        return [
            'sales_today' => $this->salesBetween($todayStart, $todayEnd),
            'orders_today' => (clone $paidOrders())
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count(),
            'sales_month' => $this->salesBetween($monthStart, $monthEnd),
            'orders_month' => (clone $paidOrders())
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'pending_orders' => Order::query()->whereIn('status', $openStatuses)->count(),
            'low_stock' => Inventory::query()
                ->whereColumn('available_stock', '<=', 'reorder_level')
                ->where('available_stock', '>', 0)
                ->count(),
            'out_of_stock' => Inventory::query()->where('available_stock', '<=', 0)->count(),
            'pending_reviews' => Review::query()->where('status', 'pending')->count(),
            'new_enquiries' => Enquiry::query()->where('status', 'new')->count(),
        ];
    }

    protected function salesBetween(Carbon $from, Carbon $to): float
    {
        return (float) Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->sum('grand_total');
    }
}
