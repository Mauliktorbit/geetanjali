@extends('frontend.account.layout')

@section('title', 'My Coupons | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">My Coupons</h1>
    <div class="account-coupon-grid">
        @forelse ($coupons as $coupon)
            <article class="account-coupon">
                <strong>{{ $coupon->code }}</strong>
                <p>{{ $coupon->name }}</p>
                <small>
                    @if ($coupon->discount_type === 'percent')
                        {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2), '0'), '.') }}% off
                    @else
                        ₹{{ number_format((float) $coupon->discount_value) }} off
                    @endif
                    @if ($coupon->ends_at)
                        · till {{ $coupon->ends_at->format('d M Y') }}
                    @endif
                </small>
            </article>
        @empty
            <p class="account-empty">No active coupons right now. Check Offers for festive deals.</p>
            <a class="account-ghost-btn" href="{{ route('offers.index') }}">View Offers</a>
        @endforelse
    </div>
</section>
@endsection
