@extends('admin.layouts.app')
@section('title', 'Add CustomerGroup')
@section('content')
<div class="page-header">
    <div>
        <h1>Add CustomerGroup</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'CustomerGroups','url'=>route('admin.customer-groups.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.customer-groups.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Discount Percent</label>
                <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', $item->discount_percent ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Payment Terms Days</label>
                <input type="number" name="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', $item->payment_terms_days ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Moq</label>
                <input type="number" name="moq" class="form-control" value="{{ old('moq', $item->moq ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Credit Limit</label>
                <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit', $item->credit_limit ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.customer-groups.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection