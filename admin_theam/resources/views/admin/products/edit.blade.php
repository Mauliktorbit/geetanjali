@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Product</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>$item->name]]])
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.products.duplicate', $item) }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Duplicate?')">Duplicate</button></form>
        <form method="POST" action="{{ route('admin.products.archive', $item) }}">@csrf<button class="btn btn-ghost" onclick="return confirm('Archive?')">Archive</button></form>
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.products.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.products._form', ['item' => $item])
        <div class="form-actions">
            <button class="btn btn-primary">Update product</button>
            <a href="{{ route('admin.products.show', $item) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/admin-product-variants.js') }}"></script>
@endpush
