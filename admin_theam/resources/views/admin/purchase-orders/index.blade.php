@extends('admin.layouts.app')
@section('title','Purchase Orders')
@section('content')
<div class="page-header"><div><h1>Purchase Orders</h1></div><div class="page-actions"><a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">Create PO</a></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>PO</th><th>Supplier</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->po_number }}</td><td>{{ $item->supplier?->name }}</td><td>{{ $item->status }}</td><td>{{ money($item->total) }}</td><td>{{ $item->order_date }}</td>
<td><a href="{{ route('admin.purchase-orders.show',$item) }}" class="btn btn-sm btn-ghost">View</a><a href="{{ route('admin.purchase-orders.edit',$item) }}" class="btn btn-sm">Edit</a></td></tr>
@empty<tr><td colspan="6">None</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection