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
@endphp

<div class="page-header">
    <div>
        <h1>{{ $greeting }}, {{ $adminName }}</h1>
        <p class="subtitle">{{ now()->format('l, d F Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">View Orders</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="kpi-grid dashboard-kpis">
    @include('admin.components.kpi-card', [
        'label' => "Today's Sales",
        'value' => money($overview['sales_today'] ?? 0),
        'meta' => $ordersToday === 1 ? '1 order today' : $ordersToday.' orders today',
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
        'meta' => $pendingOrders > 0 ? 'Need your attention' : 'All caught up',
        'variant' => $pendingOrders > 0 ? 'warning' : 'success',
        'url' => route('admin.orders.index', ['status' => 'new']),
    ])
    @include('admin.components.kpi-card', [
        'label' => 'Low Stock',
        'value' => number_format($lowStock),
        'meta' => $outOfStock > 0 ? $outOfStock.' out of stock' : 'Stock looks fine',
        'variant' => $lowStock > 0 || $outOfStock > 0 ? 'danger' : 'default',
        'url' => route('admin.inventory.low-stock'),
    ])
</div>

@if ($outOfStock > 0 || $pendingReviews > 0 || $newEnquiries > 0)
    <div class="dash-attention" aria-label="Needs attention">
        @if ($outOfStock > 0)
            <a href="{{ route('admin.inventory.out-of-stock') }}">
                <span class="dash-attention__count">{{ $outOfStock }}</span>
                Out of stock
            </a>
        @endif
        @if ($pendingReviews > 0)
            <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}">
                <span class="dash-attention__count">{{ $pendingReviews }}</span>
                Reviews to approve
            </a>
        @endif
        @if ($newEnquiries > 0)
            <a href="{{ route('admin.enquiries.index') }}">
                <span class="dash-attention__count">{{ $newEnquiries }}</span>
                New messages
            </a>
        @endif
    </div>
@endif
@endsection
