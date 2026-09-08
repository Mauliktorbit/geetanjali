@extends('admin.layouts.app')
@section('title','Transfer Stock')
@section('content')
<div class="page-header"><div><h1>Transfer Stock</h1></div></div>
@include('admin.components.alerts')
<div class="card"><form method="POST" action="{{ route('admin.inventory.transfer.store') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Product</label><select name="product_id" class="form-control" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="form-group"><label>From</label><select name="from_warehouse_id" class="form-control" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>To</label><select name="to_warehouse_id" class="form-control" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="form-group"><label>Reason</label><input name="reason" class="form-control"></div>
</div>
<button class="btn btn-primary" onclick="return confirm('Transfer stock?')">Transfer</button>
</form></div>
@endsection