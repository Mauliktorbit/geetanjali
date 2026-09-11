@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('content')
<div class="page-header">
    <div>
        <h1>Coupons</h1>
        <p class="subtitle">Checkout codes customers can use for a discount.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">Add Coupon</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Discount</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.coupons.show', $item) }}" class="orders-table__number">{{ $item->name }}</a>
                    </td>
                    <td>{{ $item->discountLabel() }}</td>
                    <td>{{ $item->dateRange() }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.coupons.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->code }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.coupons.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit {{ $item->code }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">@include('admin.components.empty-state', ['title' => 'No coupons yet', 'text' => 'Add a coupon so customers can get a discount at checkout.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
