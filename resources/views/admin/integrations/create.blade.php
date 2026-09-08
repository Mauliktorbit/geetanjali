@extends('admin.layouts.app')
@section('title', 'Add Integration')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Integration</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Integrations','url'=>route('admin.integrations.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.integrations.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Provider</label>
                <input type="text" name="provider" class="form-control" value="{{ old('provider', $item->provider ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}" required>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_sandbox" value="1" {{ old('is_sandbox', $item->is_sandbox ?? true) ? 'checked' : '' }}> Is Sandbox</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection