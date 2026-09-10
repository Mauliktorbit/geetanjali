@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="subtitle">Store performance overview</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-secondary">Create Order</a>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-secondary">Add Coupon</a>
        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-secondary">Update Stock</a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="btn btn-ghost">Pending Orders</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="kpi-grid">
    @include('admin.components.kpi-card', ['label' => 'Sales Today', 'value' => money($kpis['sales']['today'] ?? 0), 'variant' => 'success'])
    @include('admin.components.kpi-card', ['label' => 'Weekly Sales', 'value' => money($kpis['sales']['weekly'] ?? 0)])
    @include('admin.components.kpi-card', ['label' => 'Monthly Sales', 'value' => money($kpis['sales']['monthly'] ?? 0)])
    @include('admin.components.kpi-card', ['label' => 'Yearly Sales', 'value' => money($kpis['sales']['yearly'] ?? 0), 'variant' => 'info'])
    @include('admin.components.kpi-card', ['label' => 'Total Orders', 'value' => number_format(collect($kpis['order_status_counts'] ?? [])->sum())])
    @include('admin.components.kpi-card', ['label' => 'Pending Orders', 'value' => number_format(($kpis['order_status_counts']['new'] ?? 0) + ($kpis['order_status_counts']['payment_pending'] ?? 0)), 'variant' => 'warning'])
    @include('admin.components.kpi-card', ['label' => 'AOV', 'value' => money($kpis['aov'] ?? 0), 'variant' => 'info'])
    @include('admin.components.kpi-card', ['label' => 'Customers', 'value' => number_format($kpis['customers']['total'] ?? 0), 'meta' => ($kpis['customers']['new_today'] ?? 0) . ' new today'])
    @include('admin.components.kpi-card', ['label' => 'Active Products', 'value' => number_format($kpis['products']['active'] ?? 0)])
    @include('admin.components.kpi-card', ['label' => 'Low Stock', 'value' => number_format($kpis['inventory']['low_stock'] ?? 0), 'variant' => 'warning'])
    @include('admin.components.kpi-card', ['label' => 'Out of Stock', 'value' => number_format($kpis['inventory']['out_of_stock'] ?? 0), 'variant' => 'danger'])
    @include('admin.components.kpi-card', ['label' => 'Pending Payments', 'value' => number_format($kpis['pending_payments'] ?? 0)])
    @include('admin.components.kpi-card', ['label' => 'Abandoned Carts', 'value' => number_format($kpis['abandoned_carts']['count'] ?? 0), 'meta' => money($kpis['abandoned_carts']['value'] ?? 0)])
</div>

<div class="grid-2 mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales (30 days)</h3>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders by Status</h3>
        </div>
        <div class="chart-container chart-doughnut">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="grid-2 mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Best Sellers</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                <tbody>
                @forelse($bestSellers as $row)
                    <tr>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ $row->qty_sold }}</td>
                        <td>{{ money($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">@include('admin.components.empty-state', ['title' => 'No sales yet', 'text' => 'Sales will appear here after orders are placed.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">View all</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Product</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->productSummary() }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>@include('admin.components.status-badge', ['status' => $order->status])</td>
                        <td>{{ money($order->grand_total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">@include('admin.components.empty-state', ['title' => 'No orders yet', 'text' => 'Create your first order to get started.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const salesLabels = @json(($salesChart ?? collect())->pluck('period'));
    const salesData = @json(($salesChart ?? collect())->pluck('revenue'));
    const statusLabels = @json(($orderStatusChart ?? collect())->pluck('status'));
    const statusData = @json(($orderStatusChart ?? collect())->pluck('total'));

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { boxWidth: 12, font: { family: 'DM Sans', size: 12 } } }
        }
    };

    const salesEl = document.getElementById('salesChart');
    if (salesEl) {
        new Chart(salesEl, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Revenue',
                    data: salesData,
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#0f766e'
                }]
            },
            options: {
                ...commonOptions,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 0 } }
                }
            }
        });
    }

    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#0d9488', '#0284c7', '#ca8a04', '#dc2626', '#7c3aed', '#64748b', '#ea580c', '#16a34a'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                ...commonOptions,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } }
                }
            }
        });
    }
})();
</script>
@endpush
