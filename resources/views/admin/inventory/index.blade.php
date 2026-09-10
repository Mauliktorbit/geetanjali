@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="page-header">
    <div>
        <h1>Inventory</h1>
        <p class="subtitle">See how many pieces you have for each product.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Inventory']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-primary">Update Stock</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or SKU" class="form-control">
        <select name="status" class="form-control" aria-label="Stock status">
            <option value="">All stock</option>
            <option value="in" @selected(request('status') === 'in')>In stock</option>
            <option value="low" @selected(request('status') === 'low')>Low stock</option>
            <option value="out" @selected(request('status') === 'out')>Out of stock</option>
        </select>
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-image">Image</th>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $stock = (int) ($item->stock_qty ?? 0);
                    $status = $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : 'in');
                    $statusLabel = $status === 'out' ? 'Out of stock' : ($status === 'low' ? 'Low stock' : 'In stock');
                    $badge = $status === 'out' ? 'inactive' : ($status === 'low' ? 'warning' : 'active');
                @endphp
                <tr>
                    <td class="col-image">
                        <img src="{{ storefront_image($item->imagePath()) }}" class="thumb-sm" alt="{{ $item->name }}">
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if ($item->sku)
                            <div class="category-product-empty">{{ $item->sku }}</div>
                        @endif
                    </td>
                    <td><strong>{{ number_format($stock) }}</strong></td>
                    <td>@include('admin.components.status-badge', ['status' => $badge, 'label' => $statusLabel])</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View stock" aria-label="View stock for {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.inventory.adjust', $item) }}" class="btn btn-sm btn-icon" title="Update stock" aria-label="Update stock for {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">@include('admin.components.empty-state', ['title' => 'No products yet', 'text' => 'Add a product first, then update its stock here.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
