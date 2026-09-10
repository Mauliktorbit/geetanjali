@extends('admin.layouts.app')
@section('title', 'Add Category')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Category</h1>
        <p class="subtitle">Enter the category name and image.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Categories', 'url' => route('admin.categories.index')], ['label' => 'Add']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.categories._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
