@extends('admin.layouts.app')
@section('title','Orders')
@section('content')
<div class="page-header"><div><h1>Orders</h1></div>
<div class="page-actions"><a href="{{ route('admin.orders.create') }}" class="btn btn-primary">Create Order</a></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar">
<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Order # / customer">
<select name="status" class="form-control"><option value="">All statuses</option>
@foreach($statuses as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach
</select>
<select name="payment_status" class="form-control"><option value="">Payment</option>
<option value="pending" @selected(request('payment_status')==='pending')>Pending</option>
<option value="paid" @selected(request('payment_status')==='paid')>Paid</option>
</select>
<input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
<input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
<button class="btn btn-secondary">Filter</button>
</form>
<table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr>
<td><a href="{{ route('admin.orders.show',$item) }}">{{ $item->order_number }}</a></td>
<td>{{ $item->customer_name }}</td>
<td>@include('admin.components.status-badge',['status'=>$item->status])</td>
<td>{{ $item->payment_status }}</td>
<td>{{ money($item->grand_total) }}</td>
<td>{{ $item->created_at?->format('d M Y H:i') }}</td>
<td><a href="{{ route('admin.orders.edit',$item) }}" class="btn btn-sm">Edit</a></td>
</tr>
@empty<tr><td colspan="7">No orders</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
