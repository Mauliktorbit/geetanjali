@extends('frontend.account.layout')

@section('title', 'My Account | Geetanjali Jewellers')

@section('account')
@php
    $dash = $dashboard;
    $initial = strtoupper(mb_substr($user->name, 0, 1));
    $phone = $user->mobile ?: $user->phone ?: $customer->phone;
    $displayPhone = $phone
        ? (str_starts_with((string) $phone, '+') ? $phone : '+91 '.$phone)
        : 'Not added';
    $defaultPayment = $dash['paymentMethods']->firstWhere('is_default', true) ?? $dash['paymentMethods']->first();
    $prefs = $dash['notifications'];
@endphp

<section class="account-hero-card">
    <div class="account-hero-card__person">
        <span class="account-avatar" aria-hidden="true">{{ $initial }}</span>
        <div>
            <h1 class="font-heading">Welcome, {{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <p>{{ $displayPhone }}</p>
        </div>
    </div>
    <div class="account-hero-card__meta">
        <span>Member Since {{ $user->created_at?->format('M Y') }}</span>
        <strong>{{ $customer->membershipLabel() }}</strong>
    </div>
</section>

<div class="account-stats">
    <article class="account-stat">
        <span class="account-stat__icon"><i class="bi bi-bag-check" aria-hidden="true"></i></span>
        <div>
            <small>Total Orders</small>
            <strong>{{ $dash['stats']['orders'] }}</strong>
            <a href="{{ route('account.orders') }}">View Orders</a>
        </div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon"><i class="bi bi-heart" aria-hidden="true"></i></span>
        <div>
            <small>Wishlist Items</small>
            <strong>{{ $dash['stats']['wishlist'] }}</strong>
            <a href="{{ route('wishlist.index') }}">View Wishlist</a>
        </div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
        <div>
            <small>Coupons</small>
            <strong>{{ $dash['stats']['coupons'] }}</strong>
            <a href="{{ route('account.coupons') }}">View Coupons</a>
        </div>
    </article>
    <article class="account-stat">
        <span class="account-stat__icon"><i class="bi bi-star" aria-hidden="true"></i></span>
        <div>
            <small>Reward Points</small>
            <strong>{{ number_format($dash['stats']['rewards']) }}</strong>
            <a href="{{ route('account.rewards') }}">View Rewards</a>
        </div>
    </article>
</div>

<div class="account-mid">
    <section class="account-card">
        <h2 class="font-heading">Recent Orders</h2>
        @forelse ($dash['recentOrders'] as $order)
            <a class="account-order" href="{{ route('account.orders.show', $order['number']) }}">
                <span class="account-order__thumb">
                    @if ($order['thumb'])
                        <img src="{{ asset($order['thumb']) }}" alt="">
                    @else
                        <i class="bi bi-gem" aria-hidden="true"></i>
                    @endif
                </span>
                <span class="account-order__copy">
                    <strong>{{ $order['name'] !== '—' && $order['name'] ? $order['name'] : '#'.$order['number'] }}</strong>
                    <small>#{{ $order['number'] }} · {{ $order['date'] }} · {{ $order['items'] }} {{ \Illuminate\Support\Str::plural('item', $order['items']) }}</small>
                </span>
                <span class="account-order__price">₹{{ number_format($order['total']) }}</span>
                <span class="account-status account-status--{{ $order['tone'] }}">{{ $order['status_label'] }}</span>
            </a>
        @empty
            <p class="account-empty">You have not placed an order yet.</p>
            <a class="account-ghost-btn" href="{{ route('home') }}">Start Shopping</a>
        @endforelse
        @if ($dash['recentOrders']->isNotEmpty())
            <a class="account-ghost-btn" href="{{ route('account.orders') }}">View All Orders</a>
        @endif
    </section>

    <section class="account-card">
        <h2 class="font-heading">Saved Addresses</h2>
        @forelse (collect($dash['addresses'])->take(2) as $address)
            <article class="account-address">
                <div class="account-address__top">
                    <strong>{{ ucfirst($address->label ?: 'home') }}</strong>
                    @if ($address->is_default)
                        <span class="account-pill">Default</span>
                    @endif
                    <span class="account-address__actions">
                        <a href="{{ route('account.addresses') }}" aria-label="Edit address"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Delete address"><i class="bi bi-trash"></i></button>
                        </form>
                    </span>
                </div>
                <p>{{ $address->name }}</p>
                <p>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}</p>
                <p>{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                <p>{{ $address->phone }}</p>
            </article>
        @empty
            <p class="account-empty">No saved addresses yet.</p>
        @endforelse
        <a class="account-ghost-btn" href="{{ route('account.addresses') }}">+ Add New Address</a>
    </section>
</div>

<div class="account-bottom">
    <section class="account-card">
        <h2 class="font-heading">Account Details</h2>
        <dl class="account-dl">
            <div><dt>Name</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $displayPhone }}</dd></div>
        </dl>
        <a class="account-text-link" href="{{ route('account.profile') }}">Manage Profile <i class="bi bi-arrow-right"></i></a>
    </section>

    <section class="account-card">
        <h2 class="font-heading">Payment Methods</h2>
        @if ($defaultPayment)
            <p class="account-muted">You have {{ $dash['paymentMethods']->count() }} saved payment {{ \Illuminate\Support\Str::plural('method', $dash['paymentMethods']->count()) }}</p>
            <div class="account-card-row">
                <span>{{ $defaultPayment->maskedNumber() }}</span>
                @if ($defaultPayment->is_default)
                    <span class="account-pill">Default</span>
                @endif
            </div>
        @else
            <p class="account-empty">No saved cards yet. Add one for faster checkout.</p>
        @endif
        <a class="account-text-link" href="{{ route('account.payments') }}">Manage Payment Methods <i class="bi bi-arrow-right"></i></a>
    </section>

    <section class="account-card">
        <h2 class="font-heading">Notifications</h2>
        <ul class="account-pref-list">
            <li>Email Alerts <span>{{ $prefs['email'] ? 'On' : 'Off' }}</span></li>
            <li>Order Updates <span>{{ $prefs['order_updates'] ? 'On' : 'Off' }}</span></li>
            <li>Offers &amp; Promotions <span>{{ $prefs['offers'] ? 'On' : 'Off' }}</span></li>
        </ul>
        <a class="account-text-link" href="{{ route('account.notifications') }}">Manage Preferences <i class="bi bi-arrow-right"></i></a>
    </section>
</div>
@endsection
