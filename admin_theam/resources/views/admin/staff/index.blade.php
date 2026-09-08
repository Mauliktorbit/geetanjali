@extends('admin.layouts.app')
@section('title','Staff')
@section('content')
<div class="page-header"><div><h1>Staff</h1></div><div class="page-actions"><a href="{{ route('admin.staff.create') }}" class="btn btn-primary">Add staff</a></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Active</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->name }}</td><td>{{ $item->email }}</td><td>{{ $item->roles->pluck('name')->join(', ') }}</td><td>{{ $item->is_active?'Yes':'No' }}</td>
<td><a href="{{ route('admin.staff.edit',$item) }}" class="btn btn-sm">Edit</a>
<form method="POST" action="{{ route('admin.staff.destroy',$item) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@empty<tr><td colspan="5">None</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection