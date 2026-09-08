@extends('admin.layouts.app')
@section('title', 'Add ShippingMethod')
@section('content')
<div class="page-header">
    <div>
        <h1>Add ShippingMethod</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'ShippingMethods','url'=>route('admin.shipping-methods.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.shipping-methods.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $item->code ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Rate</label>
                <input type="number" name="rate" class="form-control" value="{{ old('rate', $item->rate ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="cod_available" value="1" {{ old('cod_available', $item->cod_available ?? true) ? 'checked' : '' }}> Cod Available</label>
            </div>
            <div class="form-group">
                <label>Cod Charges</label>
                <input type="number" name="cod_charges" class="form-control" value="{{ old('cod_charges', $item->cod_charges ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Estimated Delivery</label>
                <input type="text" name="estimated_delivery" class="form-control" value="{{ old('estimated_delivery', $item->estimated_delivery ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection