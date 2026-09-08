@extends('admin.layouts.app')
@section('title', 'Add Page')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Page</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Pages','url'=>route('admin.pages.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="4" >{{ old('content', $item->content ?? '') }}</textarea>
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
                <label>Canonical</label>
                <input type="text" name="canonical" class="form-control" value="{{ old('canonical', $item->canonical ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Og Image</label>
                <input type="file" name="og_image" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->og_image))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->og_image) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection