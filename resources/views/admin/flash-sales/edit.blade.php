@extends('admin.layouts.app')
@section('title', 'Edit FlashSale')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit FlashSale</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'FlashSales','url'=>route('admin.flash-sales.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.flash-sales.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Starts At</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $item->starts_at ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Ends At</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $item->ends_at ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Discount Percent</label>
                <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', $item->discount_percent ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection