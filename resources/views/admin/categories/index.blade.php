@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="page-header">
    <div>
        <h1>Categories</h1>
        <p class="subtitle">Add jewellery types such as Necklaces, Earrings and Rings.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Categories']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add Category</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name" class="form-control">
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->filled('search'))
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.categories.bulk') }}" id="bulk-form" data-no-loading>
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete the selected categories?')">Apply</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" data-check-all form="bulk-form" aria-label="Select all"></th>
                    <th class="col-image">Image</th>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Collections</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $imagePath = \App\Services\StorefrontCatalogService::categoryImage($item->slug, $item->image);
                @endphp
                <tr>
                    <td class="col-check"><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-form" aria-label="Select {{ $item->name }}"></td>
                    <td class="col-image">
                        <img src="{{ storefront_image($imagePath) }}" class="thumb-sm" alt="{{ $item->name }}">
                    </td>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>
                        @php $productCount = (int) ($item->products_count ?? $item->products->count()); @endphp
                        @if ($productCount > 0)
                            <a class="category-product-count" href="{{ route('admin.products.index', ['category_id' => $item->id]) }}">
                                {{ $productCount }} {{ \Illuminate\Support\Str::plural('product', $productCount) }}
                            </a>
                        @else
                            <span class="category-product-empty">No products</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $collections = $item->products
                                ->flatMap(fn ($product) => $product->collections)
                                ->unique('id')
                                ->sortBy('name')
                                ->values();
                        @endphp
                        @if ($collections->isNotEmpty())
                            <div class="category-collection-list">
                                @foreach ($collections as $collection)
                                    <a class="category-collection-pill" href="{{ route('admin.collections.show', $collection) }}">{{ $collection->name }}</a>
                                @endforeach
                            </div>
                        @else
                            <span class="category-product-empty">No collection</span>
                        @endif
                    </td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.categories.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.categories.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" data-no-loading>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-danger" type="submit" title="Delete" aria-label="Delete {{ $item->name }}" onclick="return confirm('Delete {{ $item->name }}? Products in this category will keep selling without a category.')">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">@include('admin.components.empty-state', ['title' => 'No categories yet', 'text' => 'Add a category name, such as Necklaces or Earrings.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
