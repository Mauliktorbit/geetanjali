@extends('admin.layouts.app')
@section('title', $item->order_number)
@section('content')
@php
    $statusKey = \App\Enums\OrderStatus::simpleKey((string) $item->status);
    $statusLabel = \App\Enums\OrderStatus::simpleLabel((string) $item->status);
    $statusBadge = \App\Enums\OrderStatus::badge((string) $item->status);
    $paymentLabel = \App\Enums\PaymentStatus::simpleLabel((string) $item->payment_status);
    $paymentBadge = $item->payment_status === 'paid' ? 'paid' : 'warning';
    $canCancel = in_array((string) $item->status, \App\Enums\OrderStatus::cancellable(), true);
    $ship = is_array($item->shipping_address) ? $item->shipping_address : [];
    $addressLine = trim((string) ($ship['line1'] ?? $ship['address_line1'] ?? ''));
    $addressLine2 = trim((string) ($ship['line2'] ?? $ship['address_line2'] ?? ''));
    $city = $item->shipping_city ?: ($ship['city'] ?? '');
    $state = $item->shipping_state ?: ($ship['state'] ?? '');
    $pincode = $item->shipping_pincode ?: ($ship['pincode'] ?? '');
    $addressParts = array_filter([$addressLine, $addressLine2, trim(implode(', ', array_filter([$city, $state]))), $pincode]);
@endphp

<div class="page-header">
    <div>
        <h1>{{ $item->order_number }}</h1>
        <p class="subtitle">Order details</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Orders', 'url' => route('admin.orders.index')], ['label' => $item->order_number]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.orders.edit', $item) }}" class="btn btn-primary">Edit</a>
        @if ($canCancel)
            <form method="POST" action="{{ route('admin.orders.cancel', $item) }}" data-no-loading>
                @csrf
                <input type="hidden" name="reason" value="Cancelled by admin">
                <button class="btn btn-danger" type="submit" onclick="return confirm('Cancel this order?')">Cancel order</button>
            </form>
        @endif
        <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $statusBadge, 'label' => $statusLabel])</span>
        </div>
        <div class="detail-item">
            <span class="label">Payment</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $paymentBadge, 'label' => $paymentLabel])</span>
        </div>
        <div class="detail-item">
            <span class="label">Date</span>
            <span class="value">{{ optional($item->created_at)->format('d M Y, h:i A') ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Total</span>
            <span class="value inventory-stock-value">{{ money($item->grand_total) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Customer</span>
            <span class="value">{{ $item->customer_name ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Phone</span>
            <span class="value">{{ $item->customer_phone ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Email</span>
            <span class="value">{{ $item->customer_email ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Delivery address</span>
            <span class="value">{{ $addressParts ? implode(', ', $addressParts) : '—' }}</span>
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
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item->items as $line)
                        <tr>
                            <td class="col-image">
                                <img src="{{ storefront_image($line->imagePath()) }}" class="thumb-sm" alt="{{ $line->product_name }}">
                            </td>
                            <td>
                                <strong>{{ $line->product_name }}</strong>
                                @if ($line->sku)
                                    <div class="category-product-empty">{{ $line->sku }}</div>
                                @endif
                            </td>
                            <td>{{ number_format((int) $line->quantity) }}</td>
                            <td>{{ money($line->unit_price) }}</td>
                            <td><strong>{{ money($line->total) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No items in this order.</p>
    @endif
</div>

<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.orders.status', $item) }}">
        @csrf
        <div class="form-group" style="max-width: 360px;">
            <label for="order-status">Order status</label>
            <select id="order-status" name="status" class="form-control" required>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($statusKey === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <span class="form-hint">Choose New, Confirmed, Packed, Shipped, Delivered or Cancelled.</span>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save status</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Back</a>
        </div>
    </form>
</div>
@endsection
