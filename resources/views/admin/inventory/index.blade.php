@extends('admin.layouts.app')
@section('title','Inventory')
@section('content')
<div class="page-header"><div><h1>Inventory</h1></div>
<div class="page-actions">
<a href="{{ route('admin.inventory.adjust') }}" class="btn btn-primary">Adjust</a>
<a href="{{ route('admin.inventory.transfer') }}" class="btn btn-secondary">Transfer</a>
<a href="{{ route('admin.inventory.movements') }}" class="btn btn-ghost">Movements</a>
<a href="{{ route('admin.inventory.valuation') }}" class="btn btn-ghost">Valuation</a>
<a href="{{ route('admin.inventory.ageing') }}" class="btn btn-ghost">Ageing</a>
<a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-ghost">Low stock</a>
<a href="{{ route('admin.inventory.out-of-stock') }}" class="btn btn-ghost">Out of stock</a>
</div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="warehouse_id" class="form-control"><option value="">All warehouses</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id')==$w->id)>{{ $w->name }}</option>@endforeach</select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>Product</th><th>Warehouse</th><th>Current</th><th>Available</th><th>Reserved</th><th>Reorder</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->product?->name }}</td><td>{{ $item->warehouse?->name }}</td><td>{{ $item->current_stock }}</td><td>{{ $item->available_stock }}</td><td>{{ $item->reserved_stock }}</td><td>{{ $item->reorder_level }}</td></tr>
@empty<tr><td colspan="6">No stock rows</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection