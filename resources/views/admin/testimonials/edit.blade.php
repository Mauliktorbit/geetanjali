@extends('admin.layouts.app')
@section('title', 'Edit Testimonial')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Testimonial</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Testimonials','url'=>route('admin.testimonials.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.testimonials.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Designation</label>
                <input type="text" name="designation" class="form-control" value="{{ old('designation', $item->designation ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Avatar</label>
                <input type="file" name="avatar" class="form-control" accept="image/*,.pdf">
                @if(!empty($item?->avatar))
                    <div class="mt-2"><img src="{{ asset('storage/' . $item->avatar) }}" alt="" class="thumb-sm"></div>
                @endif
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="4" required>{{ old('content', $item->content ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Rating</label>
                <input type="number" name="rating" class="form-control" value="{{ old('rating', $item->rating ?? '') }}" >
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
            <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection