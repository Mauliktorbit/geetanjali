@extends('admin.layouts.app')
@section('title','Backups')
@section('content')
<div class="page-header"><div><h1>Backups</h1></div>
<div class="page-actions"><form method="POST" action="{{ route('admin.backups.create') }}">@csrf<input type="hidden" name="type" value="database"><button class="btn btn-primary" onclick="return confirm('Create database backup?')">Create backup</button></form></div></div>
@include('admin.components.alerts')
<div class="card"><table class="data-table"><thead><tr><th>File</th><th>Type</th><th>Size</th><th>By</th><th>When</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->filename }}</td><td>{{ $item->type }}</td><td>{{ number_format($item->size/1024,1) }} KB</td><td>{{ $item->creator?->name }}</td><td>{{ $item->created_at }}</td>
<td>
<a href="{{ route('admin.backups.download',$item) }}" class="btn btn-sm">Download</a>
<form method="POST" action="{{ route('admin.backups.restore',$item) }}">@csrf<button class="btn btn-sm btn-secondary" onclick="return confirm('Restore this backup? This may overwrite data.')">Restore</button></form>
<form method="POST" action="{{ route('admin.backups.destroy',$item) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button></form>
</td></tr>
@empty<tr><td colspan="6">No backups</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection