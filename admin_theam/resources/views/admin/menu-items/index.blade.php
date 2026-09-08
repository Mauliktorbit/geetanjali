@extends('admin.layouts.app')
@section('title','Menu Items')
@section('content')
<div class="page-header"><div><h1>{{ $menu->name }} — Items</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="POST" action="{{ route('admin.menus.items.store',$menu) }}" enctype="multipart/form-data" class="form-grid">@csrf
<div class="form-group"><label>Title</label><input name="title" class="form-control" required></div>
<div class="form-group"><label>URL</label><input name="url" class="form-control"></div>
<div class="form-group"><label>Type</label><input name="type" class="form-control" value="custom"></div>
<div class="form-group"><label>Sort</label><input type="number" name="sort_order" class="form-control"></div>
<div class="form-group form-check"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="open_in_new_tab" value="1"> New tab</label></div>
<button class="btn btn-primary">Add item</button>
</form>
<table class="data-table mt-4"><thead><tr><th>Title</th><th>URL</th><th>Sort</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr>
<form method="POST" action="{{ route('admin.menus.items.update',[$menu,$item]) }}">@csrf @method('PUT')
<td><input name="title" class="form-control" value="{{ $item->title }}"></td>
<td><input name="url" class="form-control" value="{{ $item->url }}"></td>
<td><input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}"><input type="hidden" name="is_active" value="1"></td>
<td><button class="btn btn-sm">Save</button></form>
<form method="POST" action="{{ route('admin.menus.items.destroy',[$menu,$item]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button></form></td>
</tr>
@if($item->children->count())
@foreach($item->children as $child)
<tr><td>— {{ $child->title }}</td><td>{{ $child->url }}</td><td>{{ $child->sort_order }}</td><td></td></tr>
@endforeach
@endif
@empty<tr><td colspan="4">No items</td></tr>@endforelse
</tbody></table>
<form method="POST" action="{{ route('admin.menus.items.reorder',$menu) }}" class="mt-3">@csrf
<p class="subtitle">Reorder via JSON array of {id,sort_order,parent_id}</p>
<textarea name="order" class="form-control" rows="3">[]</textarea>
<button class="btn btn-secondary" onclick="return confirm('Reorder?')">Apply reorder JSON</button>
</form>
</div>
@endsection