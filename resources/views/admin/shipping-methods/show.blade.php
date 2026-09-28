@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Shipping rules', 'url' => route('admin.shipping-methods.index')], ['label' => $item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.shipping-methods.edit', $item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
<div class="card">
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">Charge</span>
            <span class="value">{{ (float) $item->rate > 0 ? '₹'.number_format((float) $item->rate, 0) : 'Free' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Delivery time</span>
            <span class="value">{{ $item->estimated_delivery ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Free above</span>
            <span class="value">{{ $item->free_shipping_threshold ? '₹'.number_format((float) $item->free_shipping_threshold, 0) : '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">{{ $item->is_active ? 'Shown at checkout' : 'Hidden' }}</span>
        </div>
    </div>
</div>
@endsection
