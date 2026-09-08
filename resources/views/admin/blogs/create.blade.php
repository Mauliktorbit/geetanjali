@extends('admin.layouts.app')
@section('title', 'Add Blog')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Blog</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Blogs','url'=>route('admin.blogs.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data" class="form-grid">
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
                <label>Image</label>
                <input type="file" name="image" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->image))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->image) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="4" >{{ old('excerpt', $item->excerpt ?? '') }}</textarea>
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
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_published" value="1" {{ old('is_published', $item->is_published ?? true) ? 'checked' : '' }}> Is Published</label>
            </div>
            <div class="form-group">
                <label>Published At</label>
                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $item->published_at ?? '') }}" >
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection