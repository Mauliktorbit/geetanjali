@extends('admin.layouts.app')
@section('title', 'Add ReturnReason')
@section('content')
<div class="page-header">
    <div>
        <h1>Add ReturnReason</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'ReturnReasons','url'=>route('admin.return-reasons.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.return-reasons.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" required>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.return-reasons.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection