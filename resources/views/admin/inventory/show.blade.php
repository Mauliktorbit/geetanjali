@extends('admin.layouts.app')
@section('title', $item->name.' — Stock')
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Stock details for this product</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Inventory', 'url' => route('admin.inventory.index')], ['label' => $item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.adjust', $item) }}" class="btn btn-primary">Update stock</a>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card category-show">
    <div class="category-show__media">
        <img src="{{ storefront_image($item->imagePath()) }}" alt="{{ $item->name }}">
    </div>
    <div class="category-show__body">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="label">Product</span>
                <span class="value">{{ $item->name }}</span>
            </div>
            <div class="detail-item">
                <span class="label">SKU</span>
                <span class="value">{{ $item->sku ?: '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Current stock</span>
                <span class="value inventory-stock-value">{{ number_format($stock) }} {{ $stock === 1 ? 'piece' : 'pieces' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Status</span>
                <span class="value">@include('admin.components.status-badge', ['status' => $badge, 'label' => $statusLabel])</span>
            </div>
            @if ($reserved > 0)
                <div class="detail-item">
                    <span class="label">Reserved for orders</span>
                    <span class="value">{{ number_format($reserved) }}</span>
                </div>
            @endif
            <div class="detail-item">
                <span class="label">On website</span>
                <span class="value">{{ $item->is_active ? 'Yes' : 'No' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Recent stock changes</h2>
        <a class="btn btn-secondary" href="{{ route('admin.inventory.adjust', $item) }}">Update stock</a>
    </div>
    @if ($movements->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Change</th>
                        <th>Stock after</th>
                        <th>Details</th>
                        <th>Updated by</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $movement)
                        @php
                            $change = (int) $movement->quantity_change;
                            $changeText = $change > 0 ? '+'.$change : (string) $change;
                        @endphp
                        <tr>
                            <td>{{ optional($movement->created_at)->format('d M Y, h:i A') ?: '—' }}</td>
                            <td>
                                <span class="inventory-change {{ $change > 0 ? 'is-plus' : ($change < 0 ? 'is-minus' : '') }}">
                                    {{ $changeText }}
                                </span>
                            </td>
                            <td>{{ number_format((int) $movement->new_quantity) }}</td>
                            <td>{{ $movement->reason ?: 'Stock update' }}</td>
                            <td>{{ $movement->user?->name ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No stock changes yet. Click Update stock to add or remove pieces.</p>
    @endif
</div>
@endsection
