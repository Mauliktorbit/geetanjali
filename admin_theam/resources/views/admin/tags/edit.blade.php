@extends('admin.layouts.app')
@section('title', 'Edit Tag')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Tag</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Tags','url'=>route('admin.tags.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.tags.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}" required>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection