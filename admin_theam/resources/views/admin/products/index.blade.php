@extends('admin.layouts.app')
@section('title', 'Products')
@section('content')
<div class="page-header">
    <div>
        <h1>Products</h1>
        <p class="subtitle">Manage catalog</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
        <a href="{{ route('admin.products.export', request()->query()) }}" class="btn btn-secondary">Export Excel</a>
        <a href="{{ route('admin.products.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-ghost">Export CSV</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="filters-bar">
        @csrf
        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
        <button class="btn btn-secondary" onclick="return confirm('Import products from this file?')">Import</button>
    </form>

    <form method="GET" class="filters-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name/SKU..." class="form-control">
        <select name="category_id" class="form-control">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="brand_id" class="form-control">
            <option value="">All brands</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected(request('brand_id')==$brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control">
            <option value="">All status</option>
            <option value="1" @selected(request('status')==='1')>Active</option>
            <option value="0" @selected(request('status')==='0')>Inactive</option>
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.products.bulk') }}" id="bulk-form">
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="archive">Archive</option>
                <option value="delete">Delete</option>
                <option value="price">Update price</option>
                <option value="category">Update category</option>
                <option value="tax">Update tax</option>
                <option value="stock">Update stock</option>
            </select>
            <input type="number" step="0.01" name="regular_price" class="form-control" placeholder="Regular price">
            <input type="number" step="0.01" name="sale_price" class="form-control" placeholder="Sale price">
            <input type="number" name="category_id" class="form-control" placeholder="Category ID">
            <input type="number" name="tax_rate_id" class="form-control" placeholder="Tax rate ID">
            <input type="number" name="warehouse_id" class="form-control" placeholder="Warehouse ID">
            <input type="number" name="stock" class="form-control" placeholder="Stock qty">
            <button class="btn btn-secondary" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-check-all></th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}"></td>
                        <td>@if($item->main_image)<img src="{{ asset('storage/'.$item->main_image) }}" class="thumb-sm" alt="">@endif</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ money($item->sale_price ?? $item->regular_price) }}</td>
                        <td>{{ $item->product_type }}</td>
                        <td>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</td>
                        <td class="actions">
                            <a href="{{ route('admin.products.show', $item) }}" class="btn btn-sm btn-ghost">View</a>
                            <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-sm">Edit</a>
                            <button form="dup-{{ $item->id }}" class="btn btn-sm btn-secondary" onclick="return confirm('Duplicate product?')">Duplicate</button>
                            <button form="delete-{{ $item->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">@include('admin.components.empty-state', ['title' => 'No products', 'text' => 'Create your first product.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
    @foreach($items as $item)
        <form id="delete-{{ $item->id }}" method="POST" action="{{ route('admin.products.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
        <form id="dup-{{ $item->id }}" method="POST" action="{{ route('admin.products.duplicate', $item) }}" class="d-none">@csrf</form>
    @endforeach
    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
