@extends('admin.layouts.app')
@section('title', 'Reviews')
@section('content')
<div class="page-header">
    <div>
        <h1>Reviews</h1>
        <p class="subtitle">Customer ratings after delivery. Approve to show them on the product page.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search product or customer">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="rating" class="form-control" aria-label="Rating">
            <option value="">All ratings</option>
            @for ($star = 5; $star >= 1; $star--)
                <option value="{{ $star }}" @selected((string) request('rating') === (string) $star)>{{ $star }} star{{ $star > 1 ? 's' : '' }}</option>
            @endfor
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status', 'rating']))
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.reviews.show', $item) }}" class="orders-table__number">{{ $item->productName() }}</a>
                        @if ($item->order?->order_number)
                            <div class="category-product-empty">{{ $item->order->order_number }}</div>
                        @endif
                    </td>
                    <td><strong class="orders-table__customer">{{ $item->displayName() }}</strong></td>
                    <td>{{ (int) $item->rating }} / 5</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.reviews.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View review">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            @if ($item->isPending())
                                <form method="POST" action="{{ route('admin.reviews.approve', $item) }}" data-no-loading>
                                    @csrf
                                    <button class="btn btn-sm" type="submit">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reviews.reject', $item) }}" data-no-loading data-confirm="Reject this review? It will not show on the product page.">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" type="submit">Reject</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">@include('admin.components.empty-state', ['title' => 'No reviews yet', 'text' => 'When a customer reviews a delivered order, it will appear here for approval.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
