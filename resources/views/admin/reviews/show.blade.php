@extends('admin.layouts.app')
@section('title', 'Review')
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->productName() }}</h1>
        <p class="subtitle">Customer review</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Reviews', 'url' => route('admin.reviews.index')], ['label' => $item->displayName()]]])
    </div>
    <div class="page-actions">
        @if ($item->isPending())
            <form method="POST" action="{{ route('admin.reviews.approve', $item) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.reviews.reject', $item) }}" data-confirm="Reject this review? It will not show on the product page.">
                @csrf
                <button class="btn btn-danger" type="submit">Reject</button>
            </form>
        @endif
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-ghost">Back</a>
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
            <span class="label">Product</span>
            <span class="value">
                @if ($item->product && ! $item->product->trashed())
                    <a href="{{ route('admin.products.show', $item->product) }}">{{ $item->productName() }}</a>
                @else
                    {{ $item->productName() }}
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Customer</span>
            <span class="value">{{ $item->displayName() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Rating</span>
            <span class="value">{{ (int) $item->rating }} / 5</span>
        </div>
        <div class="detail-item">
            <span class="label">Comment</span>
            <span class="value">{{ $item->comment ?: '—' }}</span>
        </div>
        @if ($item->order?->order_number)
            <div class="detail-item">
                <span class="label">Order</span>
                <span class="value"><a href="{{ route('admin.orders.show', $item->order) }}">{{ $item->order->order_number }}</a></span>
            </div>
        @endif
        <div class="detail-item">
            <span class="label">Date</span>
            <span class="value">{{ $item->created_at?->format('d M Y, h:i A') ?: '—' }}</span>
        </div>
    </div>
</div>
@endsection
