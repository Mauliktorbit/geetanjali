@extends('admin.layouts.app')
@section('title', 'Edit TaxRate')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit TaxRate</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'TaxRates','url'=>route('admin.tax-rates.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.tax-rates.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Hsn Sac</label>
                <input type="text" name="hsn_sac" class="form-control" value="{{ old('hsn_sac', $item->hsn_sac ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Cgst</label>
                <input type="number" name="cgst" class="form-control" value="{{ old('cgst', $item->cgst ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Sgst</label>
                <input type="number" name="sgst" class="form-control" value="{{ old('sgst', $item->sgst ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Igst</label>
                <input type="number" name="igst" class="form-control" value="{{ old('igst', $item->igst ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_inclusive" value="1" {{ old('is_inclusive', $item->is_inclusive ?? true) ? 'checked' : '' }}> Is Inclusive</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.tax-rates.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection