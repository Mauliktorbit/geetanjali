@extends('admin.layouts.app')
@section('title','Notifications')
@section('content')
<div class="page-header"><div><h1>Notifications</h1></div>
<div class="page-actions"><form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Mark all read?')">Mark all read</button></form></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>Title</th><th>Message</th><th>Read</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->title }}</td><td>{{ $item->message }}</td><td>{{ $item->is_read?'Yes':'No' }}</td>
<td><form method="POST" action="{{ route('admin.notifications.read',$item) }}">@csrf<button class="btn btn-sm">Open</button></form></td></tr>
@empty<tr><td colspan="4">No notifications</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection