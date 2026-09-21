@extends('frontend.account.layout')

@section('title', 'Order '.$order->order_number.' | Geetanjali Jewellers')

@section('account')
@php
    $tone = app(\App\Services\AccountService::class)->orderStatusTone((string) $order->status);
    $ship = is_array($order->shipping_address) ? $order->shipping_address : [];
    $isShipped = in_array((string) $order->status, [
        \App\Enums\OrderStatus::SHIPPED,
        \App\Enums\OrderStatus::OUT_FOR_DELIVERY,
    ], true);
@endphp
<section class="account-card">
    <div class="account-card__head">
        <h1 class="font-heading account-page-title">Order #{{ $order->order_number }}</h1>
        <span class="account-status account-status--{{ $tone }}">{{ \App\Enums\OrderStatus::customerLabel((string) $order->status) }}</span>
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
        <div><span>Shipping</span><strong>₹{{ number_format((float) $order->shipping_charge) }}</strong></div>
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

    @if ((string) $order->status === \App\Enums\OrderStatus::CANCELLED && $order->cancel_reason)
        <p class="account-muted">Cancelled: {{ $order->cancel_reason }}</p>
    @endif

    <div class="account-order-actions">
        <a class="account-ghost-btn" href="{{ route('pages.track-order') }}?order={{ $order->order_number }}">Track Order</a>
        @if (($reviewProducts ?? collect())->isNotEmpty())
            <a class="account-ghost-btn" href="{{ route('account.reviews') }}">Write a review</a>
        @endif
        <a class="account-text-link" href="{{ route('account.orders') }}">Back to orders</a>
    </div>
</section>

@if ($order->canCancel())
<section class="account-card account-card--form">
    <h2 class="font-heading account-page-title">Cancel this order</h2>
    <p class="account-muted">You can cancel until the order is shipped. After that, wait until it is delivered and request a return.</p>
    <form method="POST" action="{{ route('account.orders.cancel', $order->order_number) }}" class="account-form" onsubmit="return confirm('Cancel this order?');">
        @csrf
        <label>Reason
            <textarea name="reason" rows="4" maxlength="500" required placeholder="e.g. Ordered by mistake, need a different design">{{ old('reason') }}</textarea>
        </label>
        <button type="submit" class="account-danger-btn">Cancel order</button>
    </form>
</section>
@elseif ($order->canRequestReturn())
<section class="account-card account-card--form">
    <h2 class="font-heading account-page-title">Return this order</h2>
    <p class="account-muted">This order is delivered. Tell us why you want to return it and we will review your request.</p>
    <form method="POST" action="{{ route('account.returns.store', $order->order_number) }}" class="account-form">
        @csrf
        <label>Reason
            <textarea name="reason" rows="4" maxlength="500" required placeholder="e.g. Size issue, damaged item">{{ old('reason') }}</textarea>
        </label>
        <button type="submit" class="account-solid-btn">Submit return request</button>
    </form>
</section>
@elseif ($order->returns->isNotEmpty())
<section class="account-card">
    <h2 class="font-heading account-subhead">Return request</h2>
    @foreach ($order->returns as $return)
        @php $journey = $return->journey(true); @endphp
        <a class="account-return" href="{{ route('account.returns.show', $return->return_number) }}">
            <div>
                <strong>{{ $journey['product'] !== '—' ? $journey['product'] : $return->return_number }}</strong>
                <small>{{ $journey['title'] }} · {{ $journey['latest_date']?->format('d M Y') }} · {{ $journey['amount'] }}</small>
            </div>
            <span class="account-status account-status--{{ $journey['tone'] === 'success' ? 'delivered' : ($journey['tone'] === 'danger' ? 'cancelled' : ($journey['tone'] === 'info' ? 'shipped' : 'return')) }}">{{ \App\Enums\ReturnStatus::customerLabel((string) $return->status) }}</span>
        </a>
    @endforeach
    <a class="account-text-link" href="{{ route('account.returns') }}">View all returns</a>
</section>
@elseif ($isShipped)
<section class="account-card">
    <h2 class="font-heading account-subhead">Need a change?</h2>
    <p class="account-muted">This order is on the way, so it cannot be cancelled. You can request a return after it is delivered.</p>
</section>
@endif
@endsection
