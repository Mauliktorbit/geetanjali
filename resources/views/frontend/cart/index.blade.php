@extends('frontend.layouts.app')

@section('title', 'Your Cart | Geetanjali Jewellers')
@section('meta_description', 'Review your Geetanjali Jewellers cart and proceed to secure checkout.')

@section('content')
    <div
        class="cart-page"
        data-cart-page
        data-update-url="{{ route('cart.update') }}"
        data-coupon-url="{{ route('cart.coupon') }}"
        data-gift-url="{{ route('cart.gift') }}"
        data-csrf="{{ csrf_token() }}"
    >
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="cart-shell">
            <div class="cart-header">
                <div>
                    <h1 class="font-heading">
                        Your Cart
                        <span class="cart-header__count" data-cart-count-label>({{ $cart['count'] }} {{ $cart['count'] === 1 ? 'Item' : 'Items' }})</span>
                    </h1>
                    @include('frontend.components.gold-divider', ['align' => 'left'])
                    <p class="cart-header__sub">Review your items and proceed to checkout.</p>
                </div>
                <a href="{{ route('products.new-arrivals') }}" class="cart-continue">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Continue Shopping
                </a>
            </div>

            @if (session('success'))
                <div class="cart-flash cart-flash--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="cart-flash cart-flash--error">{{ session('error') }}</div>
            @endif

            @if ($cart['count'] < 1)
                <div class="cart-empty">
                    <h2 class="font-heading">Your cart is empty</h2>
                    <p>Discover our latest jewellery collections and add pieces you love.</p>
                    <a href="{{ route('products.new-arrivals') }}" class="cart-btn cart-btn--primary">Continue Shopping</a>
                </div>
            @else
                <div class="cart-layout">
                    <div class="cart-main">
                        <div class="cart-table-card">
                            <div class="cart-table-head">
                                <span>Product</span>
                                <span>Price</span>
                                <span>Quantity</span>
                                <span>Total</span>
                                <span class="visually-hidden">Remove</span>
                            </div>

                            <div class="cart-table-body" data-cart-items>
                                @foreach ($cart['items'] as $item)
                                    @include('frontend.components.cart.item', ['item' => $item])
                                @endforeach
                            </div>
                        </div>

                        <button type="button" class="cart-gift" data-gift-open aria-expanded="false" aria-controls="giftMessagePanel">
                            <span class="cart-gift__icon" aria-hidden="true"><i class="bi bi-gift"></i></span>
                            <span class="cart-gift__copy">
                                <strong>Add a Gift Message</strong>
                                <small>Make your gift even more special.</small>
                            </span>
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>

                        <div class="cart-gift-panel" id="giftMessagePanel" hidden>
                            <label for="gift-message">Gift Message</label>
                            <textarea id="gift-message" name="gift_message" rows="4" maxlength="500" placeholder="Write a short message for your gift...">{{ $cart['gift_message'] }}</textarea>
                            <button type="button" class="cart-btn cart-btn--primary" data-gift-save>Save Gift Message</button>
                        </div>
                    </div>

                    <aside class="cart-sidebar">
                        <div class="cart-summary">
                            <h2 class="font-heading">Order Summary</h2>
                            @include('frontend.components.gold-divider', ['align' => 'center'])

                            <div class="cart-summary__row">
                                <span data-label-subtotal>Subtotal ({{ $cart['count'] }} Items)</span>
                                <strong data-subtotal>₹{{ number_format($cart['subtotal']) }}</strong>
                            </div>
                            <div class="cart-summary__row cart-summary__row--discount">
                                <span>Discount</span>
                                <strong data-discount>- ₹{{ number_format($cart['discount']) }}</strong>
                            </div>
                            <div class="cart-summary__row">
                                <span>Shipping</span>
                                <strong>At checkout</strong>
                            </div>

                            <hr class="cart-summary__divider">

                            <div class="cart-summary__total">
                                <span>Estimated Total</span>
                                <strong data-total>₹{{ number_format($cart['total']) }}</strong>
                            </div>
                            <p class="cart-summary__save" data-savings>
                                You Save ₹{{ number_format($cart['savings']) }} on this order
                            </p>

                            <a href="{{ route('checkout.index') }}" class="cart-btn cart-btn--primary">
                                <i class="bi bi-lock-fill" aria-hidden="true"></i>
                                Proceed to Checkout
                            </a>
                            <a href="{{ route('checkout.index') }}" class="cart-btn cart-btn--outline">
                                <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                                Buy Now
                            </a>
                        </div>

                        @include('frontend.components.cart.coupons', [
                            'coupons' => $availableCoupons ?? collect(),
                            'appliedCode' => $cart['coupon'] ?? null,
                        ])

                        <div class="cart-benefits">
                            @foreach ($benefits as $benefit)
                                <div class="cart-benefits__item">
                                    <i class="bi {{ $benefit['icon'] }}" aria-hidden="true"></i>
                                    <div>
                                        <h4>{{ $benefit['title'] }}</h4>
                                        <p>{{ $benefit['subtitle'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </div>

                <section class="cart-payments" aria-label="Accepted payment methods">
                    <p>We Accept</p>
                    <div class="cart-payments__logos">
                        <span>VISA</span>
                        <span>Mastercard</span>
                        <span>RuPay</span>
                        <span>UPI</span>
                        <span>Paytm</span>
                        <span>Net Banking</span>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
