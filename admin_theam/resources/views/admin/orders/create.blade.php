@extends('admin.layouts.app')
@section('title','Create Order')
@section('content')
<div class="page-header"><div><h1>Create Order</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<form method="POST" action="{{ route('admin.orders.store') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Customer</label><select name="customer_id" class="form-control"><option value="">Guest / walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Customer name *</label><input name="customer_name" class="form-control" value="{{ old('customer_name') }}" required></div>
<div class="form-group"><label>Email</label><input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}"></div>
<div class="form-group"><label>Phone</label><input name="customer_phone" class="form-control" value="{{ old('customer_phone') }}"></div>
<div class="form-group"><label>Source</label><select name="source" class="form-control"><option value="manual">Manual</option><option value="phone">Phone</option><option value="offline">Offline</option></select></div>
<div class="form-group"><label>Payment method</label><input name="payment_method" class="form-control" value="{{ old('payment_method','cod') }}"></div>
<div class="form-group"><label>Warehouse</label><select name="warehouse_id" class="form-control"><option value="">—</option>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Shipping charge</label><input type="number" step="0.01" name="shipping_charge" class="form-control" value="0"></div>
</div>
<div class="form-grid">
<div class="form-group"><label>Ship line 1</label><input name="shipping_address[line1]" class="form-control"></div>
<div class="form-group"><label>City</label><input name="shipping_city" class="form-control"></div>
<div class="form-group"><label>State</label><input name="shipping_state" class="form-control"></div>
<div class="form-group"><label>Pincode</label><input name="shipping_pincode" class="form-control"></div>
</div>
<h3>Items</h3>
<table class="data-table"><thead><tr><th>Product</th><th>Qty</th><th>Unit price</th></tr></thead>
<tbody><tr>
<td><select name="items[0][product_id]" class="form-control" required><option value="">Select</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td>
<td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
<td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control"></td>
</tr></tbody></table>
<div class="form-actions"><button class="btn btn-primary">Create order</button></div>
</form></div>
@endsection
