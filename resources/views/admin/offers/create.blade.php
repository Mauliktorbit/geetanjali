@extends('admin.layouts.app')
@section('title', 'Add Offer')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Offer</h1>
        <p class="subtitle">This will show on the customer Offers page.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Offers', 'url' => route('admin.offers.index')], ['label' => 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card offer-form-card">
    <form method="POST" action="{{ route('admin.offers.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.offers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Publish offer</button>
            <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
