@extends('admin.layouts.app')
@section('title','Edit Order')
@section('content')
<div class="page-header"><div><h1>Edit {{ $item->order_number }}</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="POST" action="{{ route('admin.orders.update',$item) }}">@csrf @method('PUT')
<div class="form-grid">
<div class="form-group"><label>Customer name</label><input name="customer_name" class="form-control" value="{{ old('customer_name',$item->customer_name) }}"></div>
<div class="form-group"><label>Email</label><input name="customer_email" class="form-control" value="{{ old('customer_email',$item->customer_email) }}"></div>
<div class="form-group"><label>Phone</label><input name="customer_phone" class="form-control" value="{{ old('customer_phone',$item->customer_phone) }}"></div>
<div class="form-group"><label>Payment method</label><input name="payment_method" class="form-control" value="{{ old('payment_method',$item->payment_method) }}"></div>
<div class="form-group"><label>Shipping method</label><input name="shipping_method" class="form-control" value="{{ old('shipping_method',$item->shipping_method) }}"></div>
<div class="form-group"><label>Warehouse</label><select name="warehouse_id" class="form-control"><option value="">—</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected($item->warehouse_id==$w->id)>{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Shipping charge</label><input type="number" step="0.01" name="shipping_charge" class="form-control" value="{{ old('shipping_charge',$item->shipping_charge) }}"></div>
<div class="form-group"><label>COD charge</label><input type="number" step="0.01" name="cod_charge" class="form-control" value="{{ old('cod_charge',$item->cod_charge) }}"></div>
<div class="form-group span-2"><label>Customer notes</label><textarea name="customer_notes" class="form-control">{{ old('customer_notes',$item->customer_notes) }}</textarea></div>
<div class="form-group span-2"><label>Internal notes</label><textarea name="internal_notes" class="form-control">{{ old('internal_notes',$item->internal_notes) }}</textarea></div>
</div>
<div class="form-actions"><button class="btn btn-primary">Save</button><a href="{{ route('admin.orders.show',$item) }}" class="btn btn-ghost">Cancel</a></div>
</form>
</div>
@endsection
