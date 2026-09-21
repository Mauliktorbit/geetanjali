@extends('admin.layouts.app')
@section('title', 'Payments')
@section('content')
<div class="page-header">
    <div>
        <h1>Payments</h1>
        <p class="subtitle">Track customer payments, methods and refunds.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Order #, customer, transaction">
        <select name="status" class="form-control" aria-label="Payment status">
            <option value="">All statuses</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="payment_method" class="form-control" aria-label="Payment method">
            <option value="">All methods</option>
            @foreach ($methods as $key => $label)
                <option value="{{ $key }}" @selected(request('payment_method') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status', 'payment_method']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $methodLabel = $methods[$item->payment_method] ?? ucfirst(str_replace('_', ' ', (string) $item->payment_method));
                @endphp
                <tr>
                    <td>
                        @if ($item->order)
                            <a href="{{ route('admin.orders.show', $item->order) }}" class="orders-table__number">{{ $item->order->order_number }}</a>
                        @else
                            —
                        @endif
                        <div class="category-product-empty">{{ $item->transaction_id ?: 'TXN-'.$item->id }}</div>
                    </td>
                    <td>
                        <strong class="orders-table__customer">{{ $item->customer?->name ?: $item->order?->customer_name ?: '—' }}</strong>
                    </td>
                    <td>{{ $methodLabel }}</td>
                    <td class="orders-table__total">{{ money($item->amount) }}</td>
                    <td>@include('admin.components.status-badge', ['status' => \App\Enums\PaymentStatus::badge((string) $item->status), 'label' => \App\Enums\PaymentStatus::label((string) $item->status)])</td>
                    <td>{{ $item->paid_at?->format('d M Y') ?: $item->created_at?->format('d M Y') ?: '—' }}</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.payments.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View payment">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">@include('admin.components.empty-state', ['title' => 'No payments', 'text' => 'Payments appear here when customers place orders on the website.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
