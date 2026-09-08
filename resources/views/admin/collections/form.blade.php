@php
    $isEdit = ! empty($item);
    $types = ['kundan' => 'Kundan', 'bridal' => 'Bridal', 'festive' => 'Festive', 'diamond' => 'Diamond', 'gold' => 'Gold', 'custom' => 'Custom'];
@endphp
@extends('admin.layouts.app')
@section('title', $isEdit ? 'Edit Collection' : 'Add Collection')
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $isEdit ? 'Edit Collection' : 'Add Collection' }}</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Collections','url'=>route('admin.collections.index')], ['label'=> $isEdit ? 'Edit' : 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ $isEdit ? route('admin.collections.update', $item) : route('admin.collections.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $item?->name) }}" required>
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $item?->slug) }}" placeholder="Auto from name if empty">
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $item?->type ?? 'custom') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $item?->sort_order ?? 0) }}">
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @if(!empty($item?->image))
                <div class="mt-2"><img src="{{ asset('storage/'.$item->image) }}" alt="" class="thumb-sm"></div>
            @endif
        </div>
        <div class="form-group">
            <label>Banner</label>
            <input type="file" name="banner" class="form-control" accept="image/*">
            @if(!empty($item?->banner))
                <div class="mt-2"><img src="{{ asset('storage/'.$item->banner) }}" alt="" class="thumb-sm"></div>
            @endif
        </div>
        <div class="form-group" style="grid-column:1 / -1">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $item?->description) }}</textarea>
        </div>
        <div class="form-group">
            <label>SEO Title</label>
            <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $item?->seo_title) }}">
        </div>
        <div class="form-group">
            <label>SEO Description</label>
            <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $item?->seo_description) }}</textarea>
        </div>
        <div class="form-group form-check">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }}> Active</label>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
            <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
