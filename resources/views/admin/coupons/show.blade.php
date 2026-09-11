@extends('admin.layouts.app')
@section('title', $item->code)
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->code }}</h1>
        <p class="subtitle">{{ $item->name }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => $item->code]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.coupons.edit', $item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Back</a>
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
            <span class="label">Code</span>
            <span class="value">{{ $item->code }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Name</span>
            <span class="value">{{ $item->name }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Discount</span>
            <span class="value">{{ $item->discountLabel() }} ({{ $item->typeLabel() }})</span>
        </div>
        <div class="detail-item">
            <span class="label">Dates</span>
            <span class="value">{{ $item->dateRange() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Minimum order</span>
            <span class="value">{{ $item->minimum_cart !== null ? money($item->minimum_cart) : 'No minimum' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Times used</span>
            <span class="value">{{ number_format((int) $item->usage_count) }}</span>
        </div>
    </div>
</div>
@endsection
