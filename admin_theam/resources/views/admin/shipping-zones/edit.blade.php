@extends('admin.layouts.app')
@section('title', 'Edit ShippingZone')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit ShippingZone</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'ShippingZones','url'=>route('admin.shipping-zones.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.shipping-zones.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.shipping-zones.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection