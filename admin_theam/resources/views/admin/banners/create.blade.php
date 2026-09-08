@extends('admin.layouts.app')
@section('title', 'Add Banner')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Banner</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Banners','url'=>route('admin.banners.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->image))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->image) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Mobile Image</label>
                <input type="file" name="mobile_image" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->mobile_image))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->mobile_image) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Link</label>
                <input type="text" name="link" class="form-control" value="{{ old('link', $item->link ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="4" >{{ old('content', $item->content ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Starts At</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $item->starts_at ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Ends At</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $item->ends_at ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $item->sort_order ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection