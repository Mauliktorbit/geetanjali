@extends('admin.layouts.app')
@section('title', 'Offers')
@section('content')
<div class="page-header">
    <div>
        <h1>Offers</h1>
        <p class="subtitle">Cards shown on the customer Offers page.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">Add Offer</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search headline">
        <select name="category" class="form-control" aria-label="Category">
            <option value="">All categories</option>
            @foreach ($categories as $option)
                <option value="{{ $option->slug }}" @selected(request('category') === $option->slug)>{{ $option->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status', 'category']))
            <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-image">Photo</th>
                    <th>Offer</th>
                    <th>Category</th>
                    <th>Valid till</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="col-image">
                        @if ($item->image)
                            <img src="{{ storefront_image($item->image) }}" class="thumb-sm" alt="{{ $item->title }}">
                        @else
                            <span class="thumb-sm thumb-sm--empty" aria-hidden="true"></span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.offers.show', $item) }}" class="orders-table__number">{{ $item->discount_display }} {{ $item->discount_suffix }}</a>
                        <div class="form-hint">{{ str_replace(["\r", "\n"], ' ', $item->title) }}</div>
                    </td>
                    <td>{{ $item->categoryLabel() }}</td>
                    <td>{{ $item->validTillAdmin() }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.offers.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View offer">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.offers.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit offer">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.offers.destroy', $item) }}" data-no-loading>
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-sm btn-icon btn-danger"
                                    type="submit"
                                    title="Delete"
                                    aria-label="Delete offer"
                                    onclick="return confirm('Remove this offer from the website?')"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">@include('admin.components.empty-state', [
                        'title' => 'No offers yet',
                        'text' => 'Add an offer and it will appear on the customer Offers page.',
                        'actionUrl' => route('admin.offers.create'),
                        'actionLabel' => 'Add Offer',
                    ])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
