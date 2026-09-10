@extends('admin.layouts.app')
@section('title', 'Edit Category')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Category</h1>
        <p class="subtitle">Update the category name and image.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Categories', 'url' => route('admin.categories.index')], ['label' => 'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.categories.update', $item) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.categories._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
