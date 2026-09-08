<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function salesReport(array $filters = []): Collection
    {
        $query = Order::query()
            ->whereNotIn('status', [OrderStatus::CANCELLED]);

        $this->applyDateFilters($query, $filters);

        $groupBy = $filters['group_by'] ?? 'day';
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $dateExpr = match ($groupBy) {
                'month' => "strftime('%Y-%m', created_at)",
                'week' => "strftime('%Y-%W', created_at)",
                'year' => "strftime('%Y', created_at)",
                default => "date(created_at)",
            };
        } else {
            $dateExpr = match ($groupBy) {
                'month' => "DATE_FORMAT(created_at, '%Y-%m')",
                'week' => "YEARWEEK(created_at, 1)",
                'year' => "YEAR(created_at)",
                default => "DATE(created_at)",
            };
        }

        return $query
            ->selectRaw("{$dateExpr} as period")
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(subtotal) as subtotal')
            ->selectRaw('SUM(discount_amount) as discount')
            ->selectRaw('SUM(tax_amount) as tax')
            ->selectRaw('SUM(shipping_charge) as shipping')
            ->selectRaw('SUM(grand_total) as revenue')
            ->selectRaw('SUM(cost_total) as cost')
            ->selectRaw('SUM(grand_total - cost_total) as profit')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function productReport(array $filters = []): Collection
    {
        $query = OrderItem::query()
            ->select(
                'product_id',
                'product_name',
                'sku',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(cost_price * quantity) as cost'),
                DB::raw('SUM(total - (cost_price * quantity)) as profit'),
                DB::raw('COUNT(DISTINCT order_id) as orders')
            )
            ->whereHas('order', function ($q) use ($filters) {
                $q->whereNotIn('status', [OrderStatus::CANCELLED]);
                $this->applyDateFilters($q, $filters);
            });

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query
            ->groupBy('product_id', 'product_name', 'sku')
            ->orderByDesc('revenue')
            ->get();
    }

    public function customerReport(array $filters = []): Collection
    {
        $query = Customer::query()
            ->select(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                'customers.total_orders',
                'customers.total_spent',
                'customers.average_order_value',
                'customers.last_order_at'
            )
            ->withCount(['orders as period_orders' => function ($q) use ($filters) {
                $q->whereNotIn('status', [OrderStatus::CANCELLED]);
                $this->applyDateFilters($q, $filters);
            }])
            ->withSum(['orders as period_spent' => function ($q) use ($filters) {
                $q->whereNotIn('status', [OrderStatus::CANCELLED]);
                $this->applyDateFilters($q, $filters);
            }], 'grand_total');

        if (! empty($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        return $query
            ->orderByDesc('period_spent')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    public function profitabilityReport(array $filters = []): Collection
    {
        $orders = Order::query()
            ->whereNotIn('status', [OrderStatus::CANCELLED]);
        $this->applyDateFilters($orders, $filters);

        $revenue = (float) (clone $orders)->sum('grand_total');
        $cogs = (float) (clone $orders)->sum('cost_total');
        $shipping = (float) (clone $orders)->sum('shipping_charge');
        $discounts = (float) (clone $orders)->sum('discount_amount');
        $tax = (float) (clone $orders)->sum('tax_amount');

        $expenseQuery = Expense::query();
        if (! empty($filters['date_from'])) {
            $expenseQuery->whereDate('expense_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $expenseQuery->whereDate('expense_date', '<=', $filters['date_to']);
        }
        $expenses = (float) $expenseQuery->sum('amount');

        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses - $shipping;

        return collect([
            [
                'metric' => 'Revenue',
                'amount' => round($revenue, 2),
            ],
            [
                'metric' => 'COGS',
                'amount' => round($cogs, 2),
            ],
            [
                'metric' => 'Gross Profit',
                'amount' => round($grossProfit, 2),
            ],
            [
                'metric' => 'Discounts',
                'amount' => round($discounts, 2),
            ],
            [
                'metric' => 'Tax Collected',
                'amount' => round($tax, 2),
            ],
            [
                'metric' => 'Shipping Charges',
                'amount' => round($shipping, 2),
            ],
            [
                'metric' => 'Operating Expenses',
                'amount' => round($expenses, 2),
            ],
            [
                'metric' => 'Net Profit',
                'amount' => round($netProfit, 2),
            ],
            [
                'metric' => 'Gross Margin %',
                'amount' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
            ],
            [
                'metric' => 'Net Margin %',
                'amount' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0,
            ],
        ]);
    }

    protected function applyDateFilters($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
