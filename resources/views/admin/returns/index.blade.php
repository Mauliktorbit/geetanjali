@extends('admin.layouts.app')
@section('title', 'Returns')
@section('content')
<div class="page-header">
    <div>
        <h1>Returns &amp; Refunds</h1>
        <p class="subtitle">Customer return requests from delivered orders.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Return # / product / order / customer">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All statuses</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.returns.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Return</th>
                    <th>Product</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.returns.show', $item) }}" class="orders-table__number">{{ $item->return_number }}</a>
                        <div class="category-product-empty">{{ $item->created_at?->format('d M Y') ?: '—' }}</div>
                    </td>
                    <td>{{ $item->productSummary() }}</td>
                    <td>
                        @if ($item->order)
                            <a href="{{ route('admin.orders.show', $item->order) }}">{{ $item->order->order_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td><strong class="orders-table__customer">{{ $item->customerDisplayName() }}</strong></td>
                    <td>@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])
                        @php
                            $journeyDate = $item->completed_at ?: $item->rejected_at ?: $item->approved_at ?: $item->created_at;
                        @endphp
                        @if ($journeyDate)
                            <div class="category-product-empty">{{ $journeyDate->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td class="orders-table__total">{{ money($item->refund_amount) }}</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.returns.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->return_number }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">@include('admin.components.empty-state', ['title' => 'No returns', 'text' => 'Returns appear here when a customer asks to return a delivered order.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
