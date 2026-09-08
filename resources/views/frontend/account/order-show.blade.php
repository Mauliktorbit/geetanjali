@extends('frontend.account.layout')

@section('title', 'Order '.$order->order_number.' | Geetanjali Jewellers')

@section('account')
@php
    $tone = app(\App\Services\AccountService::class)->orderStatusTone((string) $order->status);
    $ship = is_array($order->shipping_address) ? $order->shipping_address : [];
@endphp
<section class="account-card">
    <div class="account-card__head">
        <h1 class="font-heading account-page-title">Order #{{ $order->order_number }}</h1>
        <span class="account-status account-status--{{ $tone }}">{{ \App\Enums\OrderStatus::label((string) $order->status) }}</span>
    </div>
    <p class="account-muted">Placed on {{ $order->created_at?->format('d M Y, h:i A') }}</p>

    <ul class="account-order-items">
        @foreach ($order->items as $item)
            <li>
                <span>
                    <strong>{{ $item->product_name }}</strong>
                    <small>Qty {{ $item->quantity }}@if($item->variant_label) · {{ $item->variant_label }}@endif</small>
                </span>
                <em>₹{{ number_format((float) $item->total) }}</em>
            </li>
        @endforeach
    </ul>

    <div class="account-totals">
        <div><span>Subtotal</span><strong>₹{{ number_format((float) $order->subtotal) }}</strong></div>
        <div><span>Discount</span><strong>- ₹{{ number_format((float) $order->discount_amount) }}</strong></div>
        <div><span>Shipping</span><strong>{{ (float) $order->shipping_charge > 0 ? '₹'.number_format((float) $order->shipping_charge) : 'FREE' }}</strong></div>
        <div class="is-total"><span>Total</span><strong>₹{{ number_format((float) $order->grand_total) }}</strong></div>
    </div>

    @if ($ship)
        <h2 class="font-heading account-subhead">Delivery Address</h2>
        <p class="account-muted">
            {{ $ship['name'] ?? $order->customer_name }}<br>
            {{ $ship['address_line1'] ?? '' }}{{ !empty($ship['address_line2']) ? ', '.$ship['address_line2'] : '' }}<br>
            {{ $ship['city'] ?? $order->shipping_city }}, {{ $ship['state'] ?? $order->shipping_state }} - {{ $ship['pincode'] ?? $order->shipping_pincode }}
        </p>
    @endif

    <div class="account-order-actions">
        <a class="account-ghost-btn" href="{{ route('pages.track-order') }}?order={{ $order->order_number }}">Track Order</a>
        <a class="account-text-link" href="{{ route('account.orders') }}">Back to orders</a>
    </div>
</section>
@endsection
