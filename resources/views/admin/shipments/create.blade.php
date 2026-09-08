@extends('admin.layouts.app')
@section('title','Create Shipment')
@section('content')
<div class="page-header"><div><h1>Create Shipment</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.shipments.store') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Order</label><select name="order_id" class="form-control" required>@foreach($orders as $o)<option value="{{ $o->id }}" @selected(($order_id??null)==$o->id)>{{ $o->order_number }} — {{ $o->customer_name }}</option>@endforeach</select></div>
<div class="form-group"><label>Courier</label><select name="courier_id" class="form-control"><option value="">—</option>@foreach($couriers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="form-group"><label>COD amount</label><input type="number" step="0.01" name="cod_amount" class="form-control"></div>
</div>
<button class="btn btn-primary">Create</button></form></div>
@endsection