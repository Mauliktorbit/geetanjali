@extends('admin.layouts.app')
@section('title',$item->return_number)
@section('content')
<div class="page-header"><div><h1>{{ $item->return_number }}</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<p>Order <a href="{{ route('admin.orders.show',$item->order_id) }}">{{ $item->order?->order_number }}</a> · {{ $item->customer?->name }} · Status {{ $item->status }}</p>
<p>Reason: {{ $item->reason?->name }} · {{ $item->customer_reason }}</p>
<table class="data-table"><thead><tr><th>Item</th><th>Qty</th><th>Refund</th></tr></thead><tbody>
@foreach($item->items as $ri)<tr><td>{{ $ri->orderItem?->product_name }}</td><td>{{ $ri->quantity }}</td><td>{{ money($ri->refund_amount) }}</td></tr>@endforeach
</tbody></table>
<div class="filters-bar mt-3">
<form method="POST" action="{{ route('admin.returns.approve',$item) }}">@csrf<input name="note" class="form-control" placeholder="Note"><button class="btn btn-secondary" onclick="return confirm('Approve?')">Approve</button></form>
<form method="POST" action="{{ route('admin.returns.reject',$item) }}">@csrf<input name="reason" class="form-control" required placeholder="Reject reason"><button class="btn btn-danger" onclick="return confirm('Reject?')">Reject</button></form>
<form method="POST" action="{{ route('admin.returns.inspect',$item) }}">@csrf<select name="inspection_status" class="form-control"><option value="passed">Passed</option><option value="failed">Failed</option><option value="partial">Partial</option></select><input name="notes" class="form-control" placeholder="Notes"><button class="btn btn-secondary">Inspect</button></form>
<form method="POST" action="{{ route('admin.returns.refund',$item) }}">@csrf<button class="btn btn-primary" onclick="return confirm('Refund?')">Refund</button></form>
<form method="POST" action="{{ route('admin.returns.replacement',$item) }}">@csrf<button class="btn btn-ghost" onclick="return confirm('Create replacement order?')">Replacement</button></form>
</div></div>
@endsection