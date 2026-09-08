@extends('admin.layouts.app')
@section('title', 'Edit ShippingClass')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit ShippingClass</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'ShippingClasss','url'=>route('admin.shipping-classes.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.shipping-classes.update', $item) }}" enctype="multipart/form-data" class="form-grid">
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
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" >{{ old('description', $item->description ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Cost</label>
                <input type="number" name="cost" class="form-control" value="{{ old('cost', $item->cost ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.shipping-classes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection