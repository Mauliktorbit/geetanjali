@extends('admin.layouts.app')
@section('title','Payment #'.$item->id)
@section('content')
<div class="page-header"><div><h1>Payment #{{ $item->id }}</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<p>Order: <a href="{{ route('admin.orders.show',$item->order_id) }}">{{ $item->order?->order_number }}</a></p>
<p>{{ money($item->amount) }} · {{ $item->payment_method }} · {{ $item->status }} · {{ $item->transaction_id }}</p>
<form method="POST" action="{{ route('admin.payments.status',$item) }}" class="filters-bar">@csrf
<select name="status" class="form-control"><option value="paid">Paid</option><option value="failed">Failed</option><option value="pending">Pending</option></select>
<input name="failure_reason" class="form-control" placeholder="Failure reason">
<button class="btn btn-secondary" onclick="return confirm('Update status?')">Update status</button>
</form>
<form method="POST" action="{{ route('admin.payments.refund',$item) }}" class="filters-bar mt-2">@csrf
<input type="number" step="0.01" name="amount" class="form-control" placeholder="Refund amount" required>
<input name="reason" class="form-control" placeholder="Reason">
<button class="btn btn-danger" onclick="return confirm('Refund?')">Refund</button>
</form>
<h3>Refunds</h3>
<ul>@forelse($item->refunds as $r)<li>{{ $r->refund_number }} — {{ money($r->amount) }} — {{ $r->status }}</li>@empty<li>None</li>@endforelse</ul>
</div>
@endsection