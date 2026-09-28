@extends('admin.layouts.app')
@section('title', 'Add Product')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Product</h1>
        <p class="subtitle">Add the photos, details, price, and stock customers need to see.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card product-form-card--wide">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" data-unsaved-guard>
        @csrf
        @include('admin.products._form')
        <div class="product-form-footer">
            <p class="product-form-footer__hint">Required fields are marked *</p>
            <div class="product-form-footer__actions">
                <button class="btn btn-primary" type="submit">Save product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-ghost" data-unsaved-cancel>Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
