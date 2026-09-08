@extends('admin.layouts.app')
@section('title', 'Edit Coupon')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Coupon</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Coupons','url'=>route('admin.coupons.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.coupons.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $item->code ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Discount Type</label>
                <input type="text" name="discount_type" class="form-control" value="{{ old('discount_type', $item->discount_type ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Discount Value</label>
                <input type="number" name="discount_value" class="form-control" value="{{ old('discount_value', $item->discount_value ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Starts At</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $item->starts_at ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Ends At</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $item->ends_at ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Usage Limit</label>
                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $item->usage_limit ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Per Customer Limit</label>
                <input type="number" name="per_customer_limit" class="form-control" value="{{ old('per_customer_limit', $item->per_customer_limit ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Minimum Cart</label>
                <input type="number" name="minimum_cart" class="form-control" value="{{ old('minimum_cart', $item->minimum_cart ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Maximum Discount</label>
                <input type="number" name="maximum_discount" class="form-control" value="{{ old('maximum_discount', $item->maximum_discount ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="new_customers_only" value="1" {{ old('new_customers_only', $item->new_customers_only ?? true) ? 'checked' : '' }}> New Customers Only</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_stackable" value="1" {{ old('is_stackable', $item->is_stackable ?? true) ? 'checked' : '' }}> Is Stackable</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection