@extends('admin.layouts.app')
@section('title','Low Stock')
@section('content')
<div class="page-header"><div><h1>Low Stock</h1></div></div>
<div class="card"><table class="data-table"><thead><tr><th>Product</th><th>Warehouse</th><th>Available</th><th>Reorder</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->product?->name }}</td><td>{{ $item->warehouse?->name }}</td><td>{{ $item->available_stock }}</td><td>{{ $item->reorder_level }}</td></tr>@empty<tr><td colspan="4">None</td></tr>@endforelse
</tbody></table></div>
@endsection