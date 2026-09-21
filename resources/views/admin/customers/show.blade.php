@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
@php
    $address = $item->addresses->firstWhere('is_default', true) ?: $item->addresses->first();
    $addressText = $address
        ? implode(', ', array_filter([
            $address->address_line1,
            $address->address_line2,
            $address->city,
            $address->state,
            $address->pincode,
        ]))
        : '';
@endphp
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Customer details</p>
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.customers.toggle', $item) }}" data-no-loading>
            @csrf
            <button class="btn {{ $item->is_blocked ? 'btn-primary' : 'btn-secondary' }}" type="submit" onclick="return confirm({{ json_encode($item->is_blocked ? 'Activate this customer so they can log in again?' : 'Deactivate this customer and block website login?') }})">
                {{ $item->is_blocked ? 'Activate' : 'Deactivate' }}
            </button>
        </form>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Name</span>
            <span class="value">{{ $item->name }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Email</span>
            <span class="value">
                @if ($item->email)
                    <a href="mailto:{{ $item->email }}">{{ $item->email }}</a>
                @else
                    —
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Phone</span>
            <span class="value">
                @if ($item->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $item->phone) }}">{{ $item->phone }}</a>
                @else
                    —
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</span>
        </div>
        <div class="detail-item">
            <span class="label">Orders</span>
            <span class="value">{{ number_format((int) $item->total_orders) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Spent</span>
            <span class="value">{{ money($item->total_spent) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Address</span>
            <span class="value">{{ $addressText !== '' ? $addressText : '—' }}</span>
        </div>
    </div>
</div>

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Orders</h2>
    </div>
    @if ($item->orders->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Date</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item->orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="orders-table__number">{{ $order->order_number }}</a>
                            </td>
                            <td>{{ $order->productSummary() }}</td>
                            <td>{{ optional($order->created_at)->format('d M Y') ?: '—' }}</td>
                            <td class="orders-table__total">{{ money($order->grand_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="category-product-empty">No orders yet.</p>
    @endif
</div>
@endsection
