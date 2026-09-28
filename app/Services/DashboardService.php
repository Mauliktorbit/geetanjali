<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const STORE_TIMEZONE = 'Asia/Kolkata';

    public function overview(): array
    {
        $clock = $this->clock();
        $todayStart = $clock->copy()->startOfDay()->utc();
        $todayEnd = $clock->copy()->endOfDay()->utc();
        $weekStart = $clock->copy()->startOfWeek()->utc();
        $monthStart = $clock->copy()->startOfMonth()->utc();
        $monthEnd = $clock->copy()->endOfMonth()->utc();
        $threshold = InventoryService::lowStockThreshold();
        [$lowStock, $outOfStock] = $this->stockAlerts($threshold);

        $ordersMonth = $this->countedOrders()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
        $salesMonth = $this->salesBetween($monthStart, $monthEnd);

        return [
            'sales_today' => $this->salesBetween($todayStart, $todayEnd),
            'orders_today' => $this->countedOrders()
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count(),
            'sales_week' => $this->salesBetween($weekStart, $clock->copy()->endOfDay()->utc()),
            'sales_month' => $salesMonth,
            'orders_month' => $ordersMonth,
            'aov' => $ordersMonth > 0 ? round($salesMonth / $ordersMonth, 2) : 0.0,
            'pending_orders' => Order::query()->whereIn('status', OrderStatus::pendingKeys())->count(),
            'customers' => Customer::query()->count(),
            'customers_today' => Customer::query()->whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'products_active' => Product::query()->where('is_active', true)->where('is_archived', false)->count(),
            'offers_active' => Offer::query()->where('is_active', true)->count(),
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'pending_reviews' => Review::query()->where('status', 'pending')->count(),
            'new_enquiries' => Enquiry::query()->where('status', 'new')->count(),
            'sales_chart' => $this->salesTrend(14),
            'status_counts' => $this->statusCounts(),
            'recent_orders' => $this->recentOrders(8),
            'best_sellers' => $this->bestSellers(6),
            'low_stock_items' => $this->lowStockItems(6, $threshold),
        ];
    }

    public function clock(): Carbon
    {
        return now(self::STORE_TIMEZONE);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function salesTrend(int $days): array
    {
        $clock = $this->clock();
        $from = $clock->copy()->subDays($days - 1)->startOfDay()->utc();
        $rows = $this->countedOrders()
            ->where('created_at', '>=', $from)
            ->get(['created_at', 'grand_total']);

        $byDay = [];
        foreach ($rows as $order) {
            $day = $order->created_at?->timezone(self::STORE_TIMEZONE)->toDateString();
            if ($day === null) {
                continue;
            }
            $byDay[$day] = ($byDay[$day] ?? 0) + (float) $order->grand_total;
        }

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $clock->copy()->subDays($i);
            $labels[] = $day->format('d M');
            $values[] = round((float) ($byDay[$day->toDateString()] ?? 0), 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    protected function statusCounts(): array
    {
        $raw = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $grouped = [];
        foreach ($raw as $status => $total) {
            $label = OrderStatus::simpleLabel((string) $status);
            $grouped[$label] = ($grouped[$label] ?? 0) + (int) $total;
        }

        $rows = [];
        foreach ($grouped as $label => $total) {
            $rows[] = ['label' => $label, 'total' => $total];
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $rows;
    }

    protected function recentOrders(int $limit): Collection
    {
        return Order::query()
            ->latest()
            ->limit($limit)
            ->get([
                'id', 'order_number', 'customer_name', 'status',
                'payment_status', 'grand_total', 'created_at',
            ]);
    }

    protected function bestSellers(int $limit): Collection
    {
        return OrderItem::query()
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(total) as revenue'))
            ->whereNotNull('product_id')
            ->whereHas('order', fn (Builder $q) => $q->whereNotIn('status', OrderStatus::excludedFromSales()))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get();
    }

    protected function lowStockItems(int $limit, int $threshold): Collection
    {
        return Product::query()
            ->select('products.id', 'products.name', 'products.sku')
            ->selectRaw('COALESCE(SUM(inventories.available_stock), 0) as stock')
            ->leftJoin('inventories', 'inventories.product_id', '=', 'products.id')
            ->where('products.is_archived', false)
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->havingRaw('COALESCE(SUM(inventories.available_stock), 0) > 0')
            ->havingRaw('COALESCE(SUM(inventories.available_stock), 0) <= ?', [$threshold])
            ->orderBy('stock')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function stockAlerts(int $threshold): array
    {
        $rows = Product::query()
            ->select('products.id')
            ->selectRaw('COALESCE(SUM(inventories.available_stock), 0) as stock')
            ->leftJoin('inventories', 'inventories.product_id', '=', 'products.id')
            ->where('products.is_archived', false)
            ->groupBy('products.id')
            ->get();

        $low = 0;
        $out = 0;
        foreach ($rows as $row) {
            $stock = (float) $row->stock;
            if ($stock <= 0) {
                $out++;
            } elseif ($stock <= $threshold) {
                $low++;
            }
        }

        return [$low, $out];
    }

    protected function countedOrders(): Builder
    {
        return Order::query()->whereNotIn('status', OrderStatus::excludedFromSales());
    }

    protected function salesBetween(Carbon $from, Carbon $to): float
    {
        return (float) $this->countedOrders()
            ->whereBetween('created_at', [$from, $to])
            ->sum('grand_total');
    }
}
