@extends('admin.layouts.app')
@section('title','Returns')
@section('content')
<div class="page-header"><div><h1>Returns</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control"><select name="status" class="form-control"><option value="">Status</option><option value="requested">Requested</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="refunded">Refunded</option></select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>Return</th><th>Order</th><th>Customer</th><th>Status</th><th>Amount</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->return_number }}</td><td>{{ $item->order?->order_number }}</td><td>{{ $item->customer?->name }}</td><td>{{ $item->status }}</td><td>{{ money($item->refund_amount) }}</td>
<td><a href="{{ route('admin.returns.show',$item) }}" class="btn btn-sm">View</a></td></tr>
@empty<tr><td colspan="6">No returns</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection