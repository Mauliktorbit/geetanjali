@extends('admin.layouts.app')
@section('title', $item->displayCode() ?: 'Offer')
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->discount_display }} {{ $item->discount_suffix }}</h1>
        <p class="subtitle">{{ str_replace(["\r", "\n"], ' ', $item->title) }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Offers', 'url' => route('admin.offers.index')], ['label' => $item->displayCode() ?: 'Offer']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.offers.edit', $item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Back</a>
        <form method="POST" action="{{ route('admin.offers.destroy', $item) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost" data-confirm="Remove this offer from the website?" data-confirm-danger>Remove</button>
        </form>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</span>
        </div>
        <div class="detail-item">
            <span class="label">Category</span>
            <span class="value">{{ $item->categoryLabel() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Headline</span>
            <span class="value">{{ str_replace(["\r", "\n"], ' ', $item->title) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Discount</span>
            <span class="value">{{ $item->discount_display }} {{ $item->discount_suffix }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Valid till</span>
            <span class="value">{{ $item->validTillAdmin() }}</span>
        </div>
        @if ($item->image)
            <div class="detail-item">
                <span class="label">Photo</span>
                <span class="value">
                    <img src="{{ storefront_image($item->image) }}" alt="{{ $item->title }}" class="thumb-sm">
                </span>
            </div>
        @endif
    </div>
</div>
@endsection
