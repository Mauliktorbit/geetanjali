@extends('frontend.layouts.app')

@section('title', 'Checkout | Geetanjali Jewellers')
@section('meta_description', 'Complete your Geetanjali Jewellers order with saved address and secure payment.')

@section('newsletter')
@endsection

@section('content')
@php
    $phone = $user->mobile ?: $user->phone;
    $displayPhone = $phone
        ? (str_starts_with((string) $phone, '+') ? $phone : '+91 '.$phone)
        : 'Not added';
    $expressCharge = $shippingOptions['express']['charge'];
@endphp
<div
    class="checkout-page"
    data-checkout-page
    data-base-total="{{ $cart['total'] }}"
    data-express-charge="{{ $expressCharge }}"
>
    @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

    <div class="checkout-shell">
        <header class="checkout-header">
            <h1 class="font-heading">Checkout</h1>
            @include('frontend.components.gold-divider', ['align' => 'left'])
            <p>Review your details and place your order. All jewellery is hallmarked and shipped securely.</p>
        </header>

        @if (session('success'))
            <div class="checkout-flash checkout-flash--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="checkout-flash checkout-flash--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="checkout-flash checkout-flash--error">{{ $errors->first() }}</div>
        @endif

        <form id="checkout-form" method="POST" action="{{ route('checkout.place') }}" class="checkout-layout">
            @csrf

            <div class="checkout-main">
                {{-- 1. Contact --}}
                <section class="checkout-card" data-contact-card>
                    <div class="checkout-card__head">
                        <h2 class="font-heading">Contact Information</h2>
                        <button type="button" class="checkout-text-btn" data-contact-toggle>Change</button>
                    </div>

                    <div class="checkout-contact" data-contact-view>
                        <p class="checkout-contact__name"><i class="bi bi-person" aria-hidden="true"></i> {{ $user->name }}</p>
                        <p><i class="bi bi-envelope" aria-hidden="true"></i> {{ $user->email }}</p>
                        <p><i class="bi bi-telephone" aria-hidden="true"></i> {{ $displayPhone }}</p>
                    </div>
                </section>

                {{-- 2. Address --}}
                <section class="checkout-card">
                    <div class="checkout-card__head">
                        <h2 class="font-heading">Delivery Address</h2>
                    </div>

                    <div class="checkout-address-list">
                        @forelse ($addresses as $address)
                            <div class="address-card {{ (int) $selectedAddressId === (int) $address->id ? 'is-selected' : '' }}">
                                <label class="address-card__select">
                                    <input
                                        type="radio"
                                        name="address_id"
                                        value="{{ $address->id }}"
                                        @checked((int) $selectedAddressId === (int) $address->id)
                                        required
                                    >
                                    <span class="address-card__radio" aria-hidden="true"></span>
                                    <span class="address-card__body">
                                        <span class="address-card__meta">
                                            <strong>{{ ucfirst($address->label) }}</strong>
                                            @if ($address->is_default)
                                                <span class="address-pill">Default</span>
                                            @endif
                                        </span>
                                        <span class="address-card__name">{{ $address->name }}</span>
                                        <span>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}</span>
                                        <span>{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</span>
                                        <span>{{ $address->phone }}</span>
                                    </span>
                                </label>
                                <button
                                    type="button"
                                    class="checkout-text-btn address-card__edit"
                                    data-address-edit
                                    data-id="{{ $address->id }}"
                                >Edit</button>
                            </div>
                        @empty
                            <p class="checkout-empty">No saved addresses yet. Add one to continue.</p>
                        @endforelse
                    </div>

                    <button type="button" class="checkout-add-address" data-address-add>
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Add New Address
                    </button>
                </section>

                {{-- 3. Delivery --}}
                <section class="checkout-card">
                    <div class="checkout-card__head">
                        <h2 class="font-heading">Delivery Method</h2>
                    </div>
                    @foreach ($shippingOptions as $key => $option)
                        <label class="method-card {{ $key === 'standard' ? 'is-selected' : '' }}">
                            <input type="radio" name="shipping_method" value="{{ $key }}" @checked($key === 'standard') data-shipping-option>
                            <span class="address-card__radio" aria-hidden="true"></span>
                            <span class="method-card__copy">
                                <strong>{{ $option['label'] }} — {{ $option['charge'] > 0 ? '₹'.number_format($option['charge']) : 'FREE' }}</strong>
                                <small>Delivery in {{ $option['eta'] }}</small>
                            </span>
                        </label>
                    @endforeach
                </section>

                {{-- 4. Payment --}}
                <section class="checkout-card">
                    <div class="checkout-card__head">
                        <h2 class="font-heading">Payment Method</h2>
                    </div>
                    <label class="method-card is-selected">
                        <input type="radio" name="payment_method" value="upi" checked>
                        <span class="address-card__radio" aria-hidden="true"></span>
                        <span class="method-card__copy">
                            <strong>UPI</strong>
                            <small>Google Pay / PhonePe / Paytm</small>
                        </span>
                    </label>
                    <label class="method-card">
                        <input type="radio" name="payment_method" value="card">
                        <span class="address-card__radio" aria-hidden="true"></span>
                        <span class="method-card__copy">
                            <strong>Credit / Debit Card</strong>
                            <small>Visa, Mastercard, RuPay</small>
                        </span>
                    </label>
                    <label class="method-card">
                        <input type="radio" name="payment_method" value="netbanking">
                        <span class="address-card__radio" aria-hidden="true"></span>
                        <span class="method-card__copy"><strong>Net Banking</strong></span>
                    </label>
                    <label class="method-card">
                        <input type="radio" name="payment_method" value="cod">
                        <span class="address-card__radio" aria-hidden="true"></span>
                        <span class="method-card__copy">
                            <strong>Cash on Delivery</strong>
                            <small>Pay when your jewellery arrives</small>
                        </span>
                    </label>
                    <label class="method-card">
                        <input type="radio" name="payment_method" value="wallet">
                        <span class="address-card__radio" aria-hidden="true"></span>
                        <span class="method-card__copy"><strong>Wallet</strong></span>
                    </label>
                    <p class="checkout-secure"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Safe &amp; Secure Payments · 256-bit encryption</p>
                </section>
            </div>

            <aside class="checkout-aside">
                <div class="checkout-summary">
                    <h2 class="font-heading">Your Order ({{ $cart['count'] }} {{ $cart['count'] === 1 ? 'Item' : 'Items' }})</h2>
                    @include('frontend.components.gold-divider', ['align' => 'center'])

                    <div class="checkout-summary__items">
                        @foreach ($cart['items'] as $item)
                            <div class="checkout-line">
                                <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" width="56" height="56">
                                <div>
                                    <strong>{{ $item['name'] }}</strong>
                                    <span>Qty {{ $item['quantity'] }}@if(!empty($item['metal'])) · {{ $item['metal'] }}@endif</span>
                                </div>
                                <em>₹{{ number_format($item['line_total']) }}</em>
                            </div>
                        @endforeach
                    </div>

                    <div class="checkout-totals">
                        <div><span>Subtotal</span><strong>₹{{ number_format($cart['subtotal']) }}</strong></div>
                        <div class="is-discount"><span>Discount</span><strong>- ₹{{ number_format($cart['discount']) }}</strong></div>
                        <div><span>Shipping</span><strong data-shipping-label>{{ $cart['shipping'] > 0 ? '₹'.number_format($cart['shipping']) : 'FREE' }}</strong></div>
                        <div class="is-total"><span>Total</span><strong data-order-total>₹{{ number_format($cart['total']) }}</strong></div>
                    </div>

                    @if ($cart['savings'] > 0)
                        <p class="checkout-save">You Save ₹{{ number_format($cart['savings']) }} on this order</p>
                    @endif

                    <button type="submit" class="checkout-place">Place Order</button>
                    <p class="checkout-legal">By placing this order, you agree to our <a href="{{ route('pages.terms') }}">Terms &amp; Conditions</a> and <a href="{{ route('pages.privacy') }}">Privacy Policy</a>.</p>
                </div>
            </aside>
        </form>
    </div>
</div>

{{-- Contact change --}}
<div class="checkout-modal" data-contact-modal hidden>
    <div class="checkout-modal__panel" role="dialog" aria-modal="true" aria-labelledby="checkout-contact-title">
        <div class="checkout-modal__head">
            <h3 class="font-heading" id="checkout-contact-title">Update contact</h3>
            <button type="button" class="checkout-modal__close" data-contact-close aria-label="Close">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('checkout.contact') }}" class="checkout-modal__form">
            @csrf
            <div class="checkout-modal__body checkout-form-grid">
                <label class="is-full">Full name
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>
                <label class="is-full">Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </label>
                <label class="is-full">Phone
                    <input type="tel" name="mobile" value="{{ old('mobile', $user->mobile ?: $user->phone) }}" required>
                </label>
            </div>
            <div class="checkout-modal__foot">
                <button type="submit" class="checkout-place checkout-place--small">Save</button>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="checkout-address-data">@json($addressPayloads)</script>

{{-- Address add/edit --}}
<div class="checkout-modal" data-address-modal hidden>
    <div class="checkout-modal__panel" role="dialog" aria-modal="true" aria-labelledby="checkout-address-title">
        <div class="checkout-modal__head">
            <h3 class="font-heading" id="checkout-address-title" data-address-modal-title>Add New Address</h3>
            <button type="button" class="checkout-modal__close" data-address-close aria-label="Close">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <form
            method="POST"
            action="{{ route('checkout.address.store') }}"
            class="checkout-modal__form"
            data-address-form
            data-store-url="{{ route('checkout.address.store') }}"
            data-default-name="{{ $user->name }}"
            data-default-phone="{{ $user->mobile ?: $user->phone }}"
        >
            @csrf
            <input type="hidden" name="_method" value="POST" data-address-method>
            <div class="checkout-modal__body checkout-form-grid">
                <label>Label
                    <select name="label" required data-field="label">
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                        <option value="other">Other</option>
                    </select>
                </label>
                <label>Full name
                    <input type="text" name="name" value="{{ $user->name }}" required data-field="name">
                </label>
                <label class="is-full">Phone
                    <input type="tel" name="phone" value="{{ $user->mobile ?: $user->phone }}" required data-field="phone">
                </label>
                <label class="is-full">Address
                    <input type="text" name="address_line1" required data-field="address_line1">
                </label>
                <label class="is-full">Landmark (optional)
                    <input type="text" name="address_line2" data-field="address_line2">
                </label>
                <label>City
                    <input type="text" name="city" required data-field="city">
                </label>
                <label>State
                    <select name="state" required data-field="state">
                        <option value="">Select state</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Pincode
                    <input type="text" name="pincode" maxlength="6" required data-field="pincode">
                </label>
                <label class="is-check">
                    <input type="checkbox" name="is_default" value="1" data-field="is_default"> Set as default
                </label>
            </div>
            <div class="checkout-modal__foot">
                <button type="submit" class="checkout-place checkout-place--small">Save Address</button>
            </div>
        </form>
    </div>
</div>
@endsection
