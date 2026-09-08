@extends('admin.layouts.app')
@section('title','Shipments')
@section('content')
<div class="page-header"><div><h1>Shipments</h1></div><div class="page-actions"><a href="{{ route('admin.shipments.create') }}" class="btn btn-primary">Create</a></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="courier_id" class="form-control"><option value="">Courier</option>@foreach($couriers as $c)<option value="{{ $c->id }}" @selected(request('courier_id')==$c->id)>{{ $c->name }}</option>@endforeach</select><input name="status" value="{{ request('status') }}" class="form-control" placeholder="Status"><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>ID</th><th>Order</th><th>Courier</th><th>AWB</th><th>Tracking</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->id }}</td><td>{{ $item->order?->order_number }}</td><td>{{ $item->courier?->name }}</td><td>{{ $item->awb_number }}</td><td>{{ $item->tracking_number }}</td><td>{{ $item->status }}</td>
<td><a href="{{ route('admin.shipments.show',$item) }}" class="btn btn-sm">View</a></td></tr>
@empty<tr><td colspan="7">No shipments</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection