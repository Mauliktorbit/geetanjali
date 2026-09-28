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
        <a href="{{ route('admin.inventory.bulk', request()->only(['search', 'status', 'sort'])) }}" class="btn btn-secondary">Bulk Update Stock</a>
        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-primary">Update Stock</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-list-card">
    @php
        $sortOptions = $sortOptions ?? \App\Repositories\InventoryRepository::productSortOptions();
        $currentSort = $currentSort ?? (array_key_exists((string) request('sort'), $sortOptions) ? (string) request('sort') : 'name_asc');
        $filterQuery = request()->except(['sort', 'page']);
        $sortLink = function (string $column) use ($filterQuery, $currentSort) {
            $next = match ($column) {
                'name' => $currentSort === 'name_asc' ? 'name_desc' : 'name_asc',
                'stock' => $currentSort === 'stock_asc' ? 'stock_desc' : 'stock_asc',
                'status' => $currentSort === 'status_in' ? 'status_out' : 'status_in',
                default => 'name_asc',
            };
            if (! str_starts_with($currentSort, $column === 'status' ? 'status_' : $column.'_')) {
                $next = match ($column) {
                    'name' => 'name_asc',
                    'stock' => 'stock_asc',
                    'status' => 'status_in',
                    default => 'name_asc',
                };
            }

            return route('admin.inventory.index', array_filter($filterQuery + ['sort' => $next], fn ($value) => $value !== null && $value !== ''));
        };
        $sortState = function (string $column) use ($currentSort) {
            if ($column === 'name' && in_array($currentSort, ['name_asc', 'name_desc'], true)) {
                return $currentSort === 'name_asc' ? 'asc' : 'desc';
            }
            if ($column === 'stock' && in_array($currentSort, ['stock_asc', 'stock_desc'], true)) {
                return $currentSort === 'stock_asc' ? 'asc' : 'desc';
            }
            if ($column === 'status' && in_array($currentSort, ['status_in', 'status_out'], true)) {
                return $currentSort === 'status_in' ? 'asc' : 'desc';
            }

            return null;
        };
    @endphp
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or SKU" class="form-control">
        <select name="status" class="form-control" aria-label="Stock status" onchange="this.form.submit()">
            <option value="">All stock</option>
            <option value="in" @selected(request('status') === 'in')>In stock</option>
            <option value="low" @selected(request('status') === 'low')>Low stock</option>
            <option value="out" @selected(request('status') === 'out')>Out of stock</option>
        </select>
        <select name="sort" class="form-control" aria-label="Sort inventory" onchange="this.form.submit()">
            @foreach ($sortOptions as $value => $label)
                <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->hasAny(['search', 'status', 'sort']))
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    @if ($items->count())
        <form method="POST" action="{{ route('admin.inventory.bulk.selected') }}" id="inventory-bulk-form" data-no-loading>
            @csrf
            <div class="bulk-bar">
                <span class="bulk-count" data-bulk-count>None selected</span>
                <label class="bulk-stock-label" for="inventory-bulk-add">Stock to Add</label>
                <input
                    id="inventory-bulk-add"
                    type="number"
                    name="stock_to_add"
                    class="form-control"
                    min="1"
                    step="1"
                    value="{{ old('stock_to_add') }}"
                    placeholder="0"
                    required
                    inputmode="numeric"
                >
                <button
                    class="btn btn-secondary"
                    type="submit"
                    data-confirm="Add this stock to every selected product?"
                >Add to selected</button>
            </div>
        </form>
    @endif

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    @if ($items->count())
                        <th class="col-check"><input type="checkbox" data-check-all form="inventory-bulk-form" aria-label="Select all products"></th>
                    @endif
                    <th class="col-image">Image</th>
                    <th>
                        <a class="th-sort{{ $sortState('name') ? ' is-'.$sortState('name') : '' }}" href="{{ $sortLink('name') }}">
                            Product
                            <span class="th-sort__icon" aria-hidden="true"></span>
                        </a>
                    </th>
                    <th>
                        <a class="th-sort{{ $sortState('stock') ? ' is-'.$sortState('stock') : '' }}" href="{{ $sortLink('stock') }}">
                            Stock
                            <span class="th-sort__icon" aria-hidden="true"></span>
                        </a>
                    </th>
                    <th>
                        <a class="th-sort{{ $sortState('status') ? ' is-'.$sortState('status') : '' }}" href="{{ $sortLink('status') }}">
                            Status
                            <span class="th-sort__icon" aria-hidden="true"></span>
                        </a>
                    </th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $stock = (int) ($item->stock_qty ?? 0);
                    $status = \App\Services\InventoryService::stockStatus($stock);
                    $statusLabel = $status === 'out' ? 'Out of stock' : ($status === 'low' ? 'Low stock' : 'In stock');
                    $badge = $status === 'out' ? 'inactive' : ($status === 'low' ? 'warning' : 'active');
                @endphp
                <tr>
                    <td class="col-check">
                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="inventory-bulk-form" aria-label="Select {{ $item->name }}">
                    </td>
                    <td class="col-image">
                        <img src="{{ storefront_image($item->imagePath()) }}" class="thumb-sm" alt="{{ $item->name }}">
                    </td>
                    <td>
                        <strong data-inventory-name>{{ $item->name }}</strong>
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
