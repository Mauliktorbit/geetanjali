@extends('admin.layouts.app')
@section('title','Shipment #'.$item->id)
@section('content')
<div class="page-header"><div><h1>Shipment #{{ $item->id }}</h1></div>
<div class="page-actions"><a href="{{ route('admin.shipments.label',$item) }}" class="btn btn-secondary">Label</a></div></div>
@include('admin.components.alerts')
<div class="card">
<p>Order {{ $item->order?->order_number }} · Status {{ $item->status }} · AWB {{ $item->awb_number }} · Tracking {{ $item->tracking_number }}</p>
<form method="POST" action="{{ route('admin.shipments.assign',$item) }}" class="filters-bar">@csrf
<select name="courier_id" class="form-control" required>@foreach($couriers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
<button class="btn btn-secondary">Assign</button></form>
<form method="POST" action="{{ route('admin.shipments.track',$item) }}" class="filters-bar mt-2">@csrf
<input name="tracking_number" class="form-control" required placeholder="Tracking"><input name="status" class="form-control" placeholder="Status">
<button class="btn btn-secondary">Update tracking</button></form>
<form method="POST" action="{{ route('admin.shipments.cancel',$item) }}" class="mt-2">@csrf<input name="reason" class="form-control" placeholder="Reason"><button class="btn btn-danger" onclick="return confirm('Cancel shipment?')">Cancel</button></form>
</div>
@endsection