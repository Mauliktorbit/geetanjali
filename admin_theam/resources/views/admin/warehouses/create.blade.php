@extends('admin.layouts.app')
@section('title', 'Add Warehouse')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Warehouse</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Warehouses','url'=>route('admin.warehouses.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.warehouses.store') }}" enctype="multipart/form-data" class="form-grid">
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
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $item->address ?? '') }}" >
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $item->city ?? '') }}" >
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $item->state ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', $item->country ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Pincode</label>
                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $item->pincode ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $item->phone ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Priority</label>
                <input type="number" name="priority" class="form-control" value="{{ old('priority', $item->priority ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_default" value="1" {{ old('is_default', $item->is_default ?? true) ? 'checked' : '' }}> Is Default</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection