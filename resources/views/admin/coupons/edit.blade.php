@extends('admin.layouts.app')
@section('title', 'Edit Coupon')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Coupon</h1>
        <p class="subtitle">{{ $item->code }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => $item->code]]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.coupons.update', $item) }}">
        @csrf
        @method('PUT')
        @include('admin.coupons._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update coupon</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
