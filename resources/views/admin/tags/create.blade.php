@extends('admin.layouts.app')
@section('title', 'Add Tag')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Tag</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Tags','url'=>route('admin.tags.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.tags.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}" required>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection