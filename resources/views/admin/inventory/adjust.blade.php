@extends('admin.layouts.app')
@section('title','Adjust Stock')
@section('content')
<div class="page-header"><div><h1>Adjust Stock</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.inventory.adjust.store') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Product</label><select name="product_id" class="form-control" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach</select></div>
<div class="form-group"><label>Warehouse</label><select name="warehouse_id" class="form-control" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Qty change (+/-)</label><input type="number" name="quantity_change" class="form-control" required></div>
<div class="form-group"><label>Reason</label><input name="reason" class="form-control" required></div>
</div>
<button class="btn btn-primary" onclick="return confirm('Adjust stock?')">Save</button>
</form></div>
@endsection