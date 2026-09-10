@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
@php
    $imagePath = \App\Services\StorefrontCatalogService::categoryImage($item->slug, $item->image);
@endphp
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Category details</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Categories', 'url' => route('admin.categories.index')], ['label' => $item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.categories.edit', $item) }}" class="btn btn-primary">Edit</a>
        <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" data-no-loading>
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit" onclick="return confirm('Delete {{ $item->name }}? Products in this category will keep selling without a category.')">Delete</button>
        </form>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card category-show">
    <div class="category-show__media">
        <img src="{{ storefront_image($imagePath) }}" alt="{{ $item->name }}">
    </div>
    <div class="category-show__body">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="label">Name</span>
                <span class="value">{{ $item->name }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Products</span>
                <span class="value">{{ number_format((int) ($item->products_count ?? $item->products->count())) }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Collections</span>
                <span class="value">
                    @php
                        $collections = $item->products
                            ->flatMap(fn ($product) => $product->collections)
                            ->unique('id')
                            ->sortBy('name')
                            ->values();
                    @endphp
                    @if ($collections->isNotEmpty())
                        <span class="category-collection-list">
                            @foreach ($collections as $collection)
                                <a class="category-collection-pill" href="{{ route('admin.collections.show', $collection) }}">{{ $collection->name }}</a>
                            @endforeach
                        </span>
                    @else
                        No collection
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Products in {{ $item->name }}</h2>
        <a class="btn btn-secondary" href="{{ route('admin.products.create', ['category_id' => $item->id]) }}">Add product</a>
    </div>
    @if ($item->products->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Collection</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item->products as $product)
                        <tr>
                            <td class="col-image">
                                <img src="{{ storefront_image($product->imagePath()) }}" class="thumb-sm" alt="{{ $product->name }}">
                            </td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->sku ?: '—' }}</td>
                            <td>
                                @if ($product->collections->isNotEmpty())
                                    <span class="category-collection-list">
                                        @foreach ($product->collections as $collection)
                                            <a class="category-collection-pill" href="{{ route('admin.collections.show', $collection) }}">{{ $collection->name }}</a>
                                        @endforeach
                                    </span>
                                @else
                                    <span class="category-product-empty">—</span>
                                @endif
                            </td>
                            <td>{{ money($product->sale_price ?? $product->regular_price) }}</td>
                            <td>@include('admin.components.status-badge', ['status' => $product->is_active ? 'active' : 'inactive', 'label' => $product->is_active ? 'Published' : 'Unpublished'])</td>
                            <td class="actions col-actions">
                                <div class="action-group">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-ghost">View</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No products are assigned to this category yet.</p>
    @endif
</div>
@endsection
