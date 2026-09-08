@extends('admin.layouts.app')
@section('title','Attribute Values')
@section('content')
<div class="page-header"><div><h1>{{ $attribute->name }} — Values</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="POST" action="{{ route('admin.attributes.values.store',$attribute) }}" class="filters-bar">@csrf
<input name="value" class="form-control" placeholder="Value" required>
<input name="color_code" class="form-control" placeholder="Color">
<input type="number" name="sort_order" class="form-control" placeholder="Sort">
<button class="btn btn-primary">Add</button>
</form>
<table class="data-table"><thead><tr><th>Value</th><th>Color</th><th>Sort</th><th></th></tr></thead><tbody>
@forelse($items as $item)
<tr>
<form method="POST" action="{{ route('admin.attributes.values.update',[$attribute,$item]) }}">@csrf @method('PUT')
<td><input name="value" class="form-control" value="{{ $item->value }}"></td>
<td><input name="color_code" class="form-control" value="{{ $item->color_code }}"></td>
<td><input type="number" name="sort_order" class="form-control" value="{{ $item->sort_order }}"></td>
<td><button class="btn btn-sm">Save</button></form>
<form method="POST" action="{{ route('admin.attributes.values.destroy',[$attribute,$item]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button></form></td>
</tr>
@empty<tr><td colspan="4">No values</td></tr>@endforelse
</tbody></table>
<div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div></div>
@endsection