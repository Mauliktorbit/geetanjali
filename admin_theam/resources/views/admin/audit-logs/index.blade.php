@extends('admin.layouts.app')
@section('title','Audit Logs')
@section('content')
<div class="page-header"><div><h1>Audit Logs</h1></div></div>
<div class="card">
<form method="GET" class="filters-bar">
<input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search">
<input name="action" value="{{ request('action') }}" class="form-control" placeholder="Action">
<input name="auditable_type" value="{{ request('auditable_type') }}" class="form-control" placeholder="Type">
<input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
<input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
<button class="btn btn-secondary">Filter</button>
</form>
<table class="data-table"><thead><tr><th>When</th><th>User</th><th>Action</th><th>Type</th><th>IP</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->created_at }}</td><td>{{ $item->user?->name }}</td><td>{{ $item->action }}</td><td>{{ class_basename($item->auditable_type ?? '') }} #{{ $item->auditable_id }}</td><td>{{ $item->ip_address }}</td></tr>
@empty<tr><td colspan="5">No logs</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection