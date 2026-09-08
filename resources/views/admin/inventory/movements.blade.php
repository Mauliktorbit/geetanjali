@extends('admin.layouts.app')
@section('title','Stock Movements')
@section('content')
<div class="page-header"><div><h1>Stock Movements</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar">
<select name="product_id" class="form-control"><option value="">Product</option>@foreach($products as $p)<option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->name }}</option>@endforeach</select>
<select name="warehouse_id" class="form-control"><option value="">Warehouse</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id')==$w->id)>{{ $w->name }}</option>@endforeach</select>
<input name="type" value="{{ request('type') }}" class="form-control" placeholder="Type"><button class="btn btn-secondary">Filter</button>
</form>
<table class="data-table"><thead><tr><th>When</th><th>Product</th><th>Warehouse</th><th>Type</th><th>Change</th><th>Reason</th><th>User</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->created_at }}</td><td>{{ $item->product?->name }}</td><td>{{ $item->warehouse?->name }}</td><td>{{ $item->type }}</td><td>{{ $item->quantity_change }}</td><td>{{ $item->reason }}</td><td>{{ $item->user?->name }}</td></tr>
@empty<tr><td colspan="7">No movements</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection