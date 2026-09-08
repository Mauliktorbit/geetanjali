@extends('admin.layouts.app')
@section('title','Stock Valuation')
@section('content')
<div class="page-header"><div><h1>Stock Valuation</h1><p class="subtitle">Total: {{ money($total) }}</p></div></div>
<div class="card"><table class="data-table"><thead><tr><th>Product</th><th>Warehouse</th><th>Qty</th><th>Value</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->product?->name }}</td><td>{{ $item->warehouse?->name }}</td><td>{{ $item->current_stock }}</td><td>{{ money($item->valuation) }}</td></tr>@endforeach
</tbody></table></div>
@endsection