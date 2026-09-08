@extends('admin.layouts.app')
@section('title','Roles')
@section('content')
<div class="page-header"><div><h1>Roles</h1></div><div class="page-actions"><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Add role</a></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>Name</th><th>Permissions</th><th>Users</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->name }}</td><td>{{ $item->permissions_count }}</td><td>{{ $item->users_count }}</td>
<td><a href="{{ route('admin.roles.edit',$item) }}" class="btn btn-sm">Edit</a>
<form method="POST" action="{{ route('admin.roles.destroy',$item) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@empty<tr><td colspan="4">None</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection