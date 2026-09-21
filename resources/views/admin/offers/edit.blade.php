@extends('admin.layouts.app')
@section('title', 'Edit Offer')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Offer</h1>
        <p class="subtitle">{{ str_replace(["\r", "\n"], ' ', $item->title) }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Offers', 'url' => route('admin.offers.index')], ['label' => 'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card offer-form-card">
    <form method="POST" action="{{ route('admin.offers.update', $item) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.offers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update offer</button>
            <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
