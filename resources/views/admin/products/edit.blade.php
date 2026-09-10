@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Product</h1>
        <p class="subtitle">Update the details shown on the product page.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>$item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('products.show', $item->slug) }}" class="btn btn-ghost" target="_blank" rel="noopener">View on website</a>
        <form method="POST" action="{{ route('admin.products.duplicate', $item) }}">@csrf<button class="btn btn-secondary" type="submit" onclick="return confirm('Duplicate this product?')">Duplicate</button></form>
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.products.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.products._form', ['item' => $item])
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update product</button>
            <a href="{{ route('admin.products.show', $item) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
