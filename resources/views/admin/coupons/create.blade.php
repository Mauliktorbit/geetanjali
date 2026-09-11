@extends('admin.layouts.app')
@section('title', 'Add Coupon')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Coupon</h1>
        <p class="subtitle">Create a discount code for checkout.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @csrf
        @include('admin.coupons._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save coupon</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
