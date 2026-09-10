@extends('admin.layouts.app')
@section('title', 'Products')
@section('content')
<div class="page-header">
    <div>
        <h1>Products</h1>
        <p class="subtitle">Jewellery listed on the website</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Products']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or SKU" class="form-control">
        <select name="category_id" class="form-control">
            <option value="">All categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="collection_id" class="form-control" aria-label="Jewellery collection">
            <option value="">Jewellery collection</option>
            @foreach ($collections ?? [] as $collection)
                <option value="{{ $collection->id }}" @selected(request('collection_id') == $collection->id)>{{ $collection->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control">
            <option value="">All status</option>
            <option value="1" @selected(request('status') === '1')>Published</option>
            <option value="0" @selected(request('status') === '0')>Unpublished</option>
        </select>
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->hasAny(['search', 'category_id', 'collection_id', 'status']))
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.products.bulk') }}" id="bulk-form" data-no-loading>
        @csrf
        <div class="bulk-bar">
            <span class="bulk-count" data-bulk-count>None selected</span>
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="activate">Publish</option>
                <option value="deactivate">Unpublish</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" type="submit" onclick="return confirm('Apply this action to the selected products?')">Apply</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" data-check-all form="bulk-form" aria-label="Select all products"></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Jewellery collection</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-form" aria-label="Select {{ $item->name }}"></td>
                    <td>
                        <div class="product-cell">
                            <img src="{{ storefront_image($item->imagePath()) }}" class="thumb-sm" alt="{{ $item->name }}">
                            <div>
                                <strong>{{ $item->name }}</strong>
                                @if ($item->badge)
                                    <div class="product-cell__badge">{{ $item->badge }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->category?->name ?: '—' }}</td>
                    <td>{{ $item->collections->pluck('name')->filter()->join(', ') ?: '—' }}</td>
                    <td>{{ money($item->sale_price ?? $item->regular_price) }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive', 'label' => $item->is_active ? 'Published' : 'Unpublished'])</td>
                    <td class="actions">
                        <a href="{{ route('admin.products.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->name }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit {{ $item->name }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $item) }}" data-no-loading>
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-icon btn-danger" type="submit" title="Delete" aria-label="Delete {{ $item->name }}" onclick="return confirm('Delete this product?')">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">@include('admin.components.empty-state', ['title' => 'No products', 'text' => 'Add a jewellery product to get started.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
