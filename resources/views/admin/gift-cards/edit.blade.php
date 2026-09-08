@extends('admin.layouts.app')
@section('title', 'Edit GiftCard')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit GiftCard</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'GiftCards','url'=>route('admin.gift-cards.index')], ['label'=>'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.gift-cards.update', $item) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

            <div class="form-group">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $item->code ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Initial Balance</label>
                <input type="number" name="initial_balance" class="form-control" value="{{ old('initial_balance', $item->initial_balance ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Balance</label>
                <input type="number" name="balance" class="form-control" value="{{ old('balance', $item->balance ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Expires At</label>
                <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $item->expires_at ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('admin.gift-cards.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection