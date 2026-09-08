@extends('admin.layouts.app')
@section('title','Stock Ageing')
@section('content')
<div class="page-header"><div><h1>Stock Ageing</h1></div></div>
<div class="card"><table class="data-table"><thead><tr><th>Product</th><th>Warehouse</th><th>Qty</th><th>Last in</th><th>Age (days)</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->product?->name }}</td><td>{{ $item->warehouse?->name }}</td><td>{{ $item->available_stock }}</td><td>{{ $item->last_in_at }}</td><td>{{ $item->age_days }}</td></tr>@endforeach
</tbody></table></div>
@endsection