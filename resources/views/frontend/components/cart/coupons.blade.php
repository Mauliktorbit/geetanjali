@php
    $coupons = $coupons ?? collect();
    $appliedCode = $appliedCode ?? null;
    $count = $coupons->count();
@endphp

<div class="cart-coupon" data-cart-coupons>
    <h3>Apply Coupon</h3>
    <form class="cart-coupon__form" data-coupon-form data-no-loading>
        @csrf
        <label class="visually-hidden" for="coupon-code">Coupon code</label>
        <input
            id="coupon-code"
            type="text"
            name="coupon"
            value="{{ $appliedCode }}"
            placeholder="Enter coupon code"
            maxlength="50"
            autocomplete="off"
        >
        <button type="submit">Apply</button>
    </form>
    <p class="cart-coupon__msg" data-coupon-msg @if ($appliedCode) data-success="1" @endif>
        @if ($appliedCode)
            Coupon {{ $appliedCode }} applied.
        @endif
    </p>

    @if ($count > 0)
        <button
            type="button"
            class="cart-coupon__view"
            data-coupon-toggle
            aria-expanded="true"
            aria-controls="cartCouponList"
        >
            <span data-coupon-toggle-label>Hide coupons</span>
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>

        <div class="cart-coupon__list" id="cartCouponList" data-coupon-list>
            <p class="cart-coupon__list-title">Available coupons ({{ $count }})</p>
            @foreach ($coupons as $coupon)
                <article
                    class="cart-coupon-card{{ ! empty($coupon['applied']) ? ' is-applied' : '' }}{{ empty($coupon['eligible']) ? ' is-locked' : '' }}"
                    data-coupon-card
                    data-coupon-code="{{ $coupon['code'] }}"
                >
                    <div class="cart-coupon-card__copy">
                        <strong>{{ $coupon['discount'] }} off</strong>
                        <span>{{ $coupon['name'] }}</span>
                        <small>
                            Code: {{ $coupon['code'] }}
                            @if (! empty($coupon['valid_until']))
                                · till {{ $coupon['valid_until'] }}
                            @endif
                        </small>
                        @if (! empty($coupon['need_more']))
                            <em>Add {{ $coupon['need_more'] }} more to use this coupon</em>
                        @elseif (! empty($coupon['min_order']))
                            <em>Min. order {{ $coupon['min_order'] }}</em>
                        @endif
                    </div>
                    @if (! empty($coupon['applied']))
                        <button type="button" class="cart-coupon-card__btn" data-coupon-remove>Remove</button>
                    @elseif (! empty($coupon['eligible']))
                        <button type="button" class="cart-coupon-card__btn" data-coupon-apply="{{ $coupon['code'] }}">Apply</button>
                    @else
                        <button type="button" class="cart-coupon-card__btn" disabled>Locked</button>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>
