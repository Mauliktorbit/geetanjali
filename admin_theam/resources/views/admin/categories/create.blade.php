@extends('admin.layouts.app')
@section('title', 'Add Category')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Category</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Categorys','url'=>route('admin.categories.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Parent Id</label>
                <input type="number" name="parent_id" class="form-control" value="{{ old('parent_id', $item->parent_id ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->image))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->image) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Banner</label>
                <input type="file" name="banner" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->banner))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->banner) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" >{{ old('description', $item->description ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Seo Title</label>
                <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $item->seo_title ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Seo Description</label>
                <textarea name="seo_description" class="form-control" rows="4" >{{ old('seo_description', $item->seo_description ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Seo Keywords</label>
                <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $item->seo_keywords ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $item->display_order ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection