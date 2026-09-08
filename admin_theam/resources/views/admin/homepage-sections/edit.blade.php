@extends('admin.layouts.app')
@section('title', 'Edit HomepageSection')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit HomepageSection</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'HomepageSections','url'=>route('admin.homepage-sections.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.homepage-sections.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}" required>
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
            <a href="{{ route('admin.homepage-sections.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection