@extends('admin.layouts.app')
@section('title', 'Add Product')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Product</h1>
        <p class="subtitle">Fill only what customers see on the product page.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
