@extends('admin.layouts.app')
@section('title', 'Edit StoreLocation')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit StoreLocation</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'StoreLocations','url'=>route('admin.store-locations.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.store-locations.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $item->address ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $item->city ?? '') }}" >
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $item->state ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Pincode</label>
                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $item->pincode ?? '') }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $item->phone ?? '') }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $item->email ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.store-locations.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection