@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $ordersToday = (int) ($overview['orders_today'] ?? 0);
    $ordersMonth = (int) ($overview['orders_month'] ?? 0);
    $pendingOrders = (int) ($overview['pending_orders'] ?? 0);
    $lowStock = (int) ($overview['low_stock'] ?? 0);
    $outOfStock = (int) ($overview['out_of_stock'] ?? 0);
    $pendingReviews = (int) ($overview['pending_reviews'] ?? 0);
    $newEnquiries = (int) ($overview['new_enquiries'] ?? 0);
    $customersToday = (int) ($overview['customers_today'] ?? 0);
    $salesChart = $overview['sales_chart'] ?? ['labels' => [], 'values' => []];
    $hasSales = collect($salesChart['values'] ?? [])->sum() > 0;
    $statusCounts = $overview['status_counts'] ?? [];
    $recentOrders = $overview['recent_orders'] ?? collect();
    $bestSellers = $overview['best_sellers'] ?? collect();
    $lowStockItems = $overview['low_stock_items'] ?? collect();
    $attention = array_filter([
        ['count' => $pendingOrders, 'label' => 'Pending orders', 'url' => route('admin.orders.index', ['status' => 'pending'])],
        ['count' => $outOfStock, 'label' => 'Out of stock', 'url' => route('admin.inventory.index', ['status' => 'out'])],
        ['count' => $pendingReviews, 'label' => 'Reviews to approve', 'url' => route('admin.reviews.index', ['status' => 'pending'])],
        ['count' => $newEnquiries, 'label' => 'New messages', 'url' => route('admin.enquiries.index')],
    ], fn ($item) => $item['count'] > 0);
@endphp

<div class="page-header dash-page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="subtitle">{{ $greeting }}, {{ $adminName }} · {{ $storeNow->format('d M Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-secondary">Update Stock</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">View Orders</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="dash">
    <div class="kpi-grid dashboard-kpis">
        @include('admin.components.kpi-card', [
            'label' => "Today's Sales",
            'value' => money($overview['sales_today'] ?? 0),
            'meta' => $ordersToday === 1 ? '1 order' : $ordersToday.' orders',
            'variant' => 'success',
            'url' => route('admin.orders.index'),
        ])
        @include('admin.components.kpi-card', [
            'label' => 'This Month',
            'value' => money($overview['sales_month'] ?? 0),
            'meta' => $ordersMonth === 1 ? '1 order' : $ordersMonth.' orders',
            'url' => route('admin.reports.sales'),
        ])
        @include('admin.components.kpi-card', [
            'label' => 'Pending Orders',
            'value' => number_format($pendingOrders),
            'meta' => $pendingOrders > 0 ? 'New, on hold or confirmed' : 'Nothing waiting',
            'variant' => $pendingOrders > 0 ? 'warning' : 'success',
            'url' => route('admin.orders.index', ['status' => 'pending']),
        ])
        @include('admin.components.kpi-card', [
            'label' => 'Low Stock',
            'value' => number_format($lowStock),
            'meta' => $outOfStock > 0 ? $outOfStock.' sold out' : 'Ready to sell',
            'variant' => $lowStock > 0 || $outOfStock > 0 ? 'danger' : 'default',
            'url' => $lowStock > 0
                ? route('admin.inventory.index', ['status' => 'low'])
                : route('admin.inventory.index', ['status' => 'out']),
        ])
    </div>

    <div class="dash-strip" aria-label="More store numbers">
        <a href="{{ route('admin.reports.sales') }}" class="dash-strip__item">
            <span>This Week</span>
            <strong>{{ money($overview['sales_week'] ?? 0) }}</strong>
        </a>
        <a href="{{ route('admin.customers.index') }}" class="dash-strip__item">
            <span>Customers</span>
            <strong>{{ number_format((int) ($overview['customers'] ?? 0)) }}</strong>
            <em>{{ $customersToday }} new today</em>
        </a>
        <a href="{{ route('admin.products.index') }}" class="dash-strip__item">
            <span>Live products</span>
            <strong>{{ number_format((int) ($overview['products_active'] ?? 0)) }}</strong>
        </a>
        <div class="dash-strip__item">
            <span>Avg. order</span>
            <strong>{{ money($overview['aov'] ?? 0) }}</strong>
        </div>
    </div>

    @if ($attention)
        <div class="dash-attention" aria-label="Needs attention">
            @foreach ($attention as $item)
                <a href="{{ $item['url'] }}">
                    <span class="dash-attention__count">{{ $item['count'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="dash-main">
        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title">Recent orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">View all</a>
            </div>
            @if ($recentOrders->isNotEmpty())
                <div class="table-responsive">
                    <table class="data-table dash-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($recentOrders->take(6) as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a>
                                    <div class="dash-muted">{{ $order->created_at?->format('d M') }}</div>
                                </td>
                                <td>{{ $order->customer_name ?: '—' }}</td>
                                <td>@include('admin.components.status-badge', ['status' => \App\Enums\OrderStatus::badge((string) $order->status), 'label' => \App\Enums\OrderStatus::simpleLabel((string) $order->status)])</td>
                                <td>{{ money($order->grand_total) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="dash-empty">No orders yet. They will appear here when a customer checks out.</p>
            @endif
        </div>

        <aside class="dash-side">
            <div class="card dash-card">
                <div class="card-header">
                    <h3 class="card-title">Quick actions</h3>
                </div>
                <div class="dash-quick">
                    <a href="{{ route('admin.products.create') }}">Add product</a>
                    <a href="{{ route('admin.inventory.adjust') }}">Update stock</a>
                    <a href="{{ route('admin.offers.create') }}">Add offer</a>
                    <a href="{{ route('admin.shipping-methods.index') }}">Shipping rules</a>
                    <a href="{{ route('admin.collections.index') }}">Collections</a>
                    <a href="{{ route('admin.customers.index') }}">Customers</a>
                </div>
            </div>

            <div class="card dash-card">
                <div class="card-header">
                    <h3 class="card-title">Orders by status</h3>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">View all</a>
                </div>
                @if (count($statusCounts))
                    <ul class="dash-status-list">
                        @foreach (array_slice($statusCounts, 0, 5) as $row)
                            <li>
                                <span>{{ $row['label'] }}</span>
                                <strong>{{ number_format($row['total']) }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="dash-empty">No order activity yet.</p>
                @endif
            </div>
        </aside>
    </div>

    @if ($hasSales)
        <div class="card dash-card chart-card">
            <div class="card-header">
                <h3 class="card-title">Sales (last 14 days)</h3>
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-ghost">Full report</a>
            </div>
            <div class="chart-container dash-chart">
                <canvas
                    data-chart="line"
                    data-label="Sales"
                    data-color="#0d9488"
                    data-labels='@json($salesChart['labels'] ?? [])'
                    data-values='@json($salesChart['values'] ?? [])'
                ></canvas>
            </div>
        </div>
    @else
        <p class="dash-chart-placeholder">Sales (last 14 days) will show here after the first paid order.</p>
    @endif

    <div class="dash-lists">
        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title">Best sellers</h3>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-ghost">Products</a>
            </div>
            @if ($bestSellers->isNotEmpty())
                <ul class="dash-plain-list">
                    @foreach ($bestSellers->take(5) as $row)
                        <li>
                            @if (! empty($row->product_id))
                                <a href="{{ route('admin.products.edit', $row->product_id) }}">{{ $row->product_name }}</a>
                            @else
                                <span>{{ $row->product_name }}</span>
                            @endif
                            <strong>{{ number_format((int) $row->qty_sold) }} sold</strong>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="dash-empty">Top products will appear after orders are placed.</p>
            @endif
        </div>

        <div class="card dash-card">
            <div class="card-header">
                <h3 class="card-title">Low stock</h3>
                <a href="{{ route('admin.inventory.index', ['status' => 'low']) }}" class="btn btn-sm btn-ghost">Inventory</a>
            </div>
            @if ($lowStockItems->isNotEmpty())
                <ul class="dash-plain-list">
                    @foreach ($lowStockItems->take(5) as $item)
                        <li>
                            <a href="{{ route('admin.products.edit', $item->id) }}">{{ $item->name }}</a>
                            <a href="{{ route('admin.inventory.adjust', $item->id) }}" class="btn btn-sm">{{ (int) $item->stock }} left · Add</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="dash-empty">No products are running low right now.</p>
            @endif
        </div>
    </div>
</div>
@endsection
