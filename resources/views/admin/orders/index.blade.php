@extends('admin.layouts.app')
@section('title', 'Orders')
@section('content')
<div class="page-header">
    <div>
        <h1>Orders</h1>
        <p class="subtitle">Search, filter and open customer orders.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">Create Order</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Order # / product / customer">
        <select name="status" class="form-control" aria-label="Order status">
            <option value="">All statuses</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="form-control" aria-label="Payment">
            <option value="">Payment</option>
            <option value="pending" @selected(request('payment_status') === 'pending')>Pending</option>
            <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" aria-label="From date">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" aria-label="To date">
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', $item) }}" class="orders-table__number">{{ $item->order_number }}</a>
                        <div class="category-product-empty">{{ $item->created_at?->format('d M Y') ?: '—' }}</div>
                    </td>
                    <td class="orders-table__product">{{ $item->productSummary() }}</td>
                    <td>
                        <strong class="orders-table__customer">{{ $item->customer_name ?: '—' }}</strong>
                    </td>
                    <td>@include('admin.components.status-badge', ['status' => \App\Enums\OrderStatus::badge((string) $item->status), 'label' => \App\Enums\OrderStatus::simpleLabel((string) $item->status)])</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->payment_status === 'paid' ? 'paid' : 'warning', 'label' => \App\Enums\PaymentStatus::simpleLabel((string) $item->payment_status)])</td>
                    <td class="orders-table__total">{{ money($item->grand_total) }}</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.orders.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View order {{ $item->order_number }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.orders.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit order {{ $item->order_number }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">@include('admin.components.empty-state', ['title' => 'No orders', 'text' => 'Create an order or wait for a customer to place one.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
