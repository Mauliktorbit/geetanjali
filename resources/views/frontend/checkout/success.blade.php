@extends('frontend.layouts.app')

@section('title', 'Order Confirmed | Geetanjali Jewellers')

@section('newsletter')
@endsection

@section('content')
@php
    $shipping = $order->shipping_address ?? [];
    $paymentStatus = $order->payment_status === 'paid' ? 'Paid' : 'Payment pending';
@endphp
<div class="checkout-page checkout-page--success">
    @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

    <div class="checkout-shell checkout-success">
        <div class="checkout-success__icon" aria-hidden="true">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="font-heading">Thank you for your order</h1>
        @include('frontend.components.gold-divider')
        <p class="checkout-success__lead">Your jewellery is being prepared with care. A confirmation has been saved to your account.</p>

        <div class="checkout-success__number">
            Order number <strong>{{ $order->order_number }}</strong>
        </div>

        <div class="checkout-success__grid">
            <article>
                <span>Payment status</span>
                <strong class="{{ $order->payment_status === 'paid' ? 'is-paid' : 'is-pending' }}">{{ $paymentStatus }}</strong>
                <small>{{ $paymentLabel }}</small>
            </article>
            <article>
                <span>Expected delivery</span>
                <strong>{{ $expectedDelivery->format('d M Y') }}</strong>
                <small>{{ $shippingLabel }} · {{ $shippingEta }}</small>
            </article>
            <article>
                <span>Deliver to</span>
                <strong>{{ $shipping['name'] ?? $order->customer_name }}</strong>
                <small>{{ $shipping['city'] ?? $order->shipping_city }} {{ $shipping['pincode'] ?? $order->shipping_pincode }}</small>
            </article>
            <article>
                <span>Order total</span>
                <strong>₹{{ number_format((float) $order->grand_total) }}</strong>
                <small>{{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'items' }}</small>
            </article>
        </div>

        <div class="checkout-success__items">
            @foreach ($order->items as $item)
                <div class="checkout-line">
                    @if (!empty($item->meta['image']))
                        <img src="{{ asset($item->meta['image']) }}" alt="" width="56" height="56">
                    @endif
                    <div>
                        <strong>{{ $item->product_name }}</strong>
                        <span>Qty {{ $item->quantity }}</span>
                    </div>
                    <em>₹{{ number_format((float) $item->total) }}</em>
                </div>
            @endforeach
        </div>

        <div class="checkout-success__actions">
            <a class="checkout-place" href="{{ route('pages.track-order', ['order_id' => $order->order_number]) }}">Track Order</a>
            <a class="checkout-place checkout-place--ghost" href="{{ route('products.new-arrivals') }}">Continue Shopping</a>
        </div>
    </div>
</div>
@endsection
