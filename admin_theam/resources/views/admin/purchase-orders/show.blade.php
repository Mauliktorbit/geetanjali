@extends('admin.layouts.app')
@section('title',$item->po_number)
@section('content')
<div class="page-header"><div><h1>{{ $item->po_number }}</h1></div>
<div class="page-actions"><a href="{{ route('admin.purchase-orders.edit',$item) }}" class="btn btn-secondary">Edit</a></div></div>
@include('admin.components.alerts')
<div class="card">
<p>{{ $item->supplier?->name }} · {{ $item->warehouse?->name }} · {{ $item->status }} · Total {{ money($item->total) }}</p>
<form method="POST" action="{{ route('admin.purchase-orders.receive',$item) }}">@csrf
<table class="data-table"><thead><tr><th>Product</th><th>Ordered</th><th>Received</th><th>Receive now</th></tr></thead><tbody>
@foreach($item->items as $i=>$line)
<tr>
<td>{{ $line->product?->name }}<input type="hidden" name="items[{{ $i }}][id]" value="{{ $line->id }}"></td>
<td>{{ $line->quantity }}</td><td>{{ $line->received_quantity }}</td>
<td><input type="number" name="items[{{ $i }}][receive_qty]" class="form-control" value="{{ max(0,$line->quantity-$line->received_quantity) }}" min="0"></td>
</tr>
@endforeach
</tbody></table>
<button class="btn btn-primary mt-3" onclick="return confirm('Receive stock?')">Receive stock</button>
</form></div>
@endsection