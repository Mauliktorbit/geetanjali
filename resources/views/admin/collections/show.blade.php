@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
@php
    $protected = \App\Services\StorefrontCatalogService::isProtectedSlug($item->slug);
@endphp
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Collection details</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Collections', 'url' => route('admin.collections.index')], ['label' => $item->name]]])
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.collections.toggle', $item) }}" data-no-loading>
            @csrf
            <button class="btn {{ $item->is_active ? 'btn-ghost' : 'btn-secondary' }}" type="submit" onclick="return confirm('{{ $item->is_active ? 'Hide '.$item->name.' from the website?' : 'Show '.$item->name.' on the website?' }}')">
                {{ $item->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <a href="{{ route('admin.collections.edit', $item) }}" class="btn btn-primary">Edit</a>
        @if (! $protected)
            <form method="POST" action="{{ route('admin.collections.destroy', $item) }}" data-no-loading>
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete {{ $item->name }}? Products stay listed and are only removed from this collection.')">Delete</button>
            </form>
        @endif
        <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card category-show">
    @if ($item->image)
        <div class="category-show__media">
            <img src="{{ storefront_image($item->image) }}" alt="{{ $item->name }}">
        </div>
    @endif
    <div class="category-show__body">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="label">Name</span>
                <span class="value">{{ $item->name }}</span>
            </div>
            <div class="detail-item">
                <span class="label">URL slug</span>
                <span class="value">{{ $item->slug }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Status</span>
                <span class="value">@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive', 'label' => $item->is_active ? 'Active' : 'Inactive'])</span>
            </div>
            <div class="detail-item">
                <span class="label">Display order</span>
                <span class="value">{{ $item->sort_order }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Details</span>
                <span class="value">{{ $item->description ?: '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">SEO title</span>
                <span class="value">{{ $item->seo_title ?: '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">SEO description</span>
                <span class="value">{{ $item->seo_description ?: '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Products</span>
                <span class="value">{{ number_format((int) ($item->products_count ?? $item->products->count())) }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Website</span>
                <span class="value"><a href="{{ \App\Services\StorefrontCatalogService::storefrontUrl($item) }}" target="_blank" rel="noopener">View page</a></span>
            </div>
        </div>
    </div>
</div>

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Products in {{ $item->name }}</h2>
        <a class="btn btn-secondary" href="{{ route('admin.products.create') }}">Add product</a>
    </div>
    @if ($item->products->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
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
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>{{ money($product->sale_price ?? $product->regular_price) }}</td>
                            <td>@include('admin.components.status-badge', ['status' => $product->is_active ? 'active' : 'inactive', 'label' => $product->is_active ? 'Published' : 'Unpublished'])</td>
                            <td class="actions col-actions">
                                <div class="action-group">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $product->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit {{ $product->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No products are assigned to this collection yet. Open a product and tick this collection name.</p>
    @endif
</div>
@endsection
