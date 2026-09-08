@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>$item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-primary">Edit</a>
        <form method="POST" action="{{ route($item->is_active ? 'admin.products.deactivate' : 'admin.products.activate', $item) }}">@csrf
            <button class="btn btn-secondary" onclick="return confirm('Change status?')">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button>
        </form>
    </div>
</div>
@include('admin.components.alerts')
<div class="grid-2">
    <div class="card">
        <h3>Details</h3>
        <dl class="detail-list">
            <dt>SKU</dt><dd>{{ $item->sku }}</dd>
            <dt>Type</dt><dd>{{ $item->product_type }}</dd>
            <dt>Category</dt><dd>{{ $item->category?->name }}</dd>
            <dt>Brand</dt><dd>{{ $item->brand?->name }}</dd>
            <dt>Price</dt><dd>{{ money($item->regular_price) }} @if($item->sale_price)/ Sale {{ money($item->sale_price) }}@endif</dd>
            <dt>Cost</dt><dd>{{ money($item->cost_price) }}</dd>
            <dt>Tax</dt><dd>{{ $item->taxRate?->name }}</dd>
            <dt>Status</dt><dd>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</dd>
        </dl>
        <p>{{ $item->short_description }}</p>
        @if($item->main_image)<img src="{{ asset('storage/'.$item->main_image) }}" alt="" style="max-width:220px">@endif
    </div>
    <div class="card">
        <h3>Variants</h3>
        <table class="data-table">
            <thead><tr><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th></tr></thead>
            <tbody>
            @forelse($item->variants as $v)
                <tr><td>{{ $v->name }}</td><td>{{ $v->sku }}</td><td>{{ money($v->price) }}</td><td>{{ $v->stock }}</td></tr>
            @empty
                <tr><td colspan="4">No variants</td></tr>
            @endforelse
            </tbody>
        </table>
        <h3 class="mt-4">Inventory</h3>
        <table class="data-table">
            <thead><tr><th>Warehouse</th><th>Available</th><th>Reserved</th></tr></thead>
            <tbody>
            @forelse($item->inventories as $inv)
                <tr><td>{{ $inv->warehouse?->name }}</td><td>{{ $inv->available_stock }}</td><td>{{ $inv->reserved_stock }}</td></tr>
            @empty
                <tr><td colspan="3">No stock records</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
