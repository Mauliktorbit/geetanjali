@extends('admin.layouts.app')
@section('title', 'Add Supplier')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Supplier</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Suppliers','url'=>route('admin.suppliers.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.suppliers.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $item->company_name ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $item->email ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $item->phone ?? '') }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
            </div>
            <div class="form-group">
                <label>Gstin</label>
                <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $item->gstin ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $item->address ?? '') }}" >
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
                <label>Country</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', $item->country ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Pincode</label>
                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $item->pincode ?? '') }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
            </div>
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $item->contact_person ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Lead Time Days</label>
                <input type="number" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $item->lead_time_days ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="4" >{{ old('notes', $item->notes ?? '') }}</textarea>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection