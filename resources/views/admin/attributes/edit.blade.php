@extends('admin.layouts.app')
@section('title', 'Edit Attribute')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Attribute</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Attributes','url'=>route('admin.attributes.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.attributes.update', $item) }}" enctype="multipart/form-data" class="form-grid">
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
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $item->sort_order ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.attributes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection