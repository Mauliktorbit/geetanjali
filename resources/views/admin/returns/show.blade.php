@extends('admin.layouts.app')
@section('title', $item->return_number)
@section('content')
@php
    $closed = in_array($item->statusKey(), [\App\Enums\ReturnStatus::REJECTED, \App\Enums\ReturnStatus::REFUNDED], true);
    $status = $item->statusKey();
@endphp
<div class="page-header">
    <div>
        <h1>{{ $item->return_number }}</h1>
        <p class="subtitle">{{ $item->productSummary() }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Returns', 'url' => route('admin.returns.index')], ['label' => $item->return_number]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.returns.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card return-show-card">
    @include('admin.returns._journey')
</div>

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Return</span>
            <span class="value">{{ $item->return_number }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Product</span>
            <span class="value">{{ $item->productSummary() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Order</span>
            <span class="value">
                @if ($item->order)
                    <a href="{{ route('admin.orders.show', $item->order) }}">{{ $item->order->order_number }}</a>
                    @if ($item->order->created_at)
                        <div class="category-product-empty">Placed {{ $item->order->created_at->format('d M Y') }}</div>
                    @endif
                @else
                    —
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Customer</span>
            <span class="value">{{ $item->customerDisplayName() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Phone</span>
            <span class="value">{{ $item->customerDisplayPhone() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Reason</span>
            <span class="value">{{ $item->customer_reason ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Refund amount</span>
            <span class="value">{{ money($item->refund_amount) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</span>
        </div>
    </div>
</div>

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Items</h2>
    </div>
    @if ($item->items->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item->items as $line)
                        @php $orderItem = $line->orderItem; @endphp
                        <tr>
                            <td class="col-image">
                                <img src="{{ storefront_image($orderItem?->imagePath()) }}" class="thumb-sm" alt="{{ $orderItem?->product_name ?: 'Product' }}">
                            </td>
                            <td>
                                <strong>{{ $orderItem?->product_name ?: 'Product' }}</strong>
                                @if ($orderItem?->sku)
                                    <div class="category-product-empty">{{ $orderItem->sku }}</div>
                                @endif
                            </td>
                            <td>{{ number_format((int) $line->quantity) }}</td>
                            <td><strong>{{ money($line->refund_amount) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No items in this return.</p>
    @endif
</div>

@if (! $closed)
    <div class="card product-form-card">
        <form method="POST" action="{{ route('admin.returns.status', $item) }}">
            @csrf
            <div class="form-group" style="max-width: 420px;">
                <label for="return-status">Next step</label>
                <select id="return-status" name="status" class="form-control" required>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($item->statusKey() === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="form-hint">
                    @if ($status === \App\Enums\ReturnStatus::REQUESTED)
                        Approve the return, reject it, or mark the money as refunded.
                    @else
                        Mark the money as refunded when it has been sent back to the customer.
                    @endif
                </span>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save</button>
                <a href="{{ route('admin.returns.index') }}" class="btn btn-ghost">Back</a>
            </div>
        </form>
    </div>
@endif
@endsection
