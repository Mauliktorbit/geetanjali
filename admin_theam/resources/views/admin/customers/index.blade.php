@extends('admin.layouts.app')
@section('title','Customers')
@section('content')
<div class="page-header"><div><h1>Customers</h1></div><div class="page-actions"><a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add Customer</a><a href="{{ route('admin.customers.export') }}" class="btn btn-secondary">Export</a></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="GET" class="filters-bar"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search"><select name="customer_group_id" class="form-control"><option value="">All groups</option>@foreach($groups as $g)<option value="{{ $g->id }}" @selected(request('customer_group_id')==$g->id)>{{ $g->name }}</option>@endforeach</select><button class="btn btn-secondary">Filter</button></form>
<table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->name }}</td><td>{{ $item->email }}</td><td>{{ $item->phone }}</td><td>{{ $item->total_orders }}</td><td>{{ money($item->total_spent) }}</td><td>{{ $item->is_blocked?'Blocked':'Active' }}</td>
<td><a href="{{ route('admin.customers.show',$item) }}" class="btn btn-sm btn-ghost">View</a><a href="{{ route('admin.customers.edit',$item) }}" class="btn btn-sm">Edit</a></td></tr>
@empty<tr><td colspan="7">No customers</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection