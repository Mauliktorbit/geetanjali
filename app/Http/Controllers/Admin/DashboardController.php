<?php

namespace App\Http\Controllers\Admin;

use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends AdminController
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $kpis = $this->dashboardService->getKpis();

        $salesChart = $this->reportService->salesReport([
            'date_from' => now()->subDays(29)->toDateString(),
            'date_to' => now()->toDateString(),
            'group_by' => 'day',
        ]);

        $orderStatusChart = collect($kpis['order_status_counts'] ?? [])
            ->map(fn ($total, $status) => ['status' => $status, 'total' => (int) $total])
            ->values();

        $paymentMethodChart = $kpis['revenue_by_payment_method'] ?? collect();
        $bestSellers = $kpis['best_sellers'] ?? collect();
        $recentOrders = $kpis['recent_orders'] ?? collect();

        return view('admin.dashboard.index', compact(
            'kpis',
            'salesChart',
            'orderStatusChart',
            'paymentMethodChart',
            'bestSellers',
            'recentOrders'
        ));
    }
}
