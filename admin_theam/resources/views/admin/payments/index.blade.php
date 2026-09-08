@extends('admin.layouts.app')
@section('title','Payments')
@section('content')
<div class="page-header"><div><h1>Payments</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="status" class="form-control"><option value="">Status</option>@foreach($statuses as $k=>$l)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $l }}</option>@endforeach</select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>ID</th><th>Order</th><th>Method</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->id }}</td><td>{{ $item->order?->order_number }}</td><td>{{ $item->payment_method }}</td><td>{{ money($item->amount) }}</td><td>{{ $item->status }}</td>
<td><a href="{{ route('admin.payments.show',$item) }}" class="btn btn-sm">View</a></td></tr>
@empty<tr><td colspan="6">No payments</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection