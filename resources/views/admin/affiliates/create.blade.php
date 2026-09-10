@extends('admin.layouts.app')
@section('title', 'Add Affiliate')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Affiliate</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Affiliates','url'=>route('admin.affiliates.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.affiliates.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
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
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $item->code ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Commission Percent</label>
                <input type="number" name="commission_percent" class="form-control" value="{{ old('commission_percent', $item->commission_percent ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.affiliates.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection