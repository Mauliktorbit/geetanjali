@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Product</h1>
        <p class="subtitle">Update the photos, details, price, and stock shown on the website.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>$item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('products.show', $item->slug) }}" class="btn btn-ghost" target="_blank" rel="noopener">View on website</a>
        <form method="POST" action="{{ route('admin.products.duplicate', $item) }}">@csrf<button class="btn btn-secondary" type="submit" onclick="return confirm('Duplicate this product?')">Duplicate</button></form>
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card product-form-card--wide">
    <form method="POST" action="{{ route('admin.products.update', $item) }}" enctype="multipart/form-data" data-unsaved-guard>
        @csrf @method('PUT')
        @include('admin.products._form', ['item' => $item])
        <div class="product-form-footer">
            <p class="product-form-footer__hint">Required fields are marked *</p>
            <div class="product-form-footer__actions">
                <button class="btn btn-primary" type="submit">Update product</button>
                <a href="{{ route('admin.products.show', $item) }}" class="btn btn-ghost" data-unsaved-cancel>Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
