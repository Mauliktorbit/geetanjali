@extends('admin.layouts.app')
@section('title','Abandoned Carts')
@section('content')
<div class="page-header"><div><h1>Abandoned Carts</h1></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>Email</th><th>Phone</th><th>Value</th><th>Status</th><th>Activity</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->email }}</td><td>{{ $item->phone }}</td><td>{{ money($item->cart_value) }}</td><td>{{ $item->recovery_status }}</td><td>{{ $item->last_activity_at }}</td>
<td><a href="{{ route('admin.abandoned-carts.show',$item) }}" class="btn btn-sm">View</a></td></tr>
@empty<tr><td colspan="6">None</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection