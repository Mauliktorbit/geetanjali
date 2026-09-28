@props([
    'order',
    'shippingLabel' => null,
    'class' => 'checkout-totals',
])

@php
    $method = trim((string) ($shippingLabel ?? ''));
@endphp

<div class="{{ $class }}">
    <div>
        <span>Subtotal</span>
        <strong>₹{{ number_format((float) $order->subtotal) }}</strong>
    </div>
    <div class="is-discount">
        <span>{{ $order->discountLineLabel() }}</span>
        <strong>- ₹{{ number_format((float) $order->discount_amount) }}</strong>
    </div>
    <div>
        <span>
            Shipping
            @if ($method !== '')
                <small>({{ $method }})</small>
            @endif
        </span>
        <strong>{{ $order->shippingAmountLabel() }}</strong>
    </div>
    <div class="is-total">
        <span>Order Total</span>
        <strong>₹{{ number_format((float) $order->grand_total) }}</strong>
    </div>
</div>
