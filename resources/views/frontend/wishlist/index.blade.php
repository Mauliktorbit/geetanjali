@extends('frontend.layouts.app')

@section('title', 'My Wishlist | Geetanjali Jewellers')
@section('meta_description', 'Your favourite Geetanjali Jewellers pieces, saved in one place.')

@section('content')
    <div
        class="wishlist-page"
        data-wishlist-page
        data-csrf="{{ csrf_token() }}"
        data-move-all-url="{{ route('wishlist.move-all') }}"
        data-share-url="{{ url()->current() }}"
    >
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="wishlist-shell">
            <div class="wishlist-header">
                <div class="wishlist-header__copy">
                    <h1 class="font-heading">
                        My Wishlist
                        <span class="wishlist-header__count" data-wishlist-count>({{ $wishlist['count'] }})</span>
                    </h1>
                    @include('frontend.components.gold-divider', ['align' => 'left'])
                    <p class="wishlist-header__sub">Your favourite pieces, all in one place. Don't miss out!</p>
                </div>

                <div class="wishlist-header__actions">
                    <button type="button" class="wishlist-btn wishlist-btn--share" data-share-wishlist>
                        <i class="bi bi-share" aria-hidden="true"></i>
                        Share Wishlist
                    </button>
                    <button
                        type="button"
                        class="wishlist-btn wishlist-btn--primary"
                        data-move-all
                        @if ($wishlist['count'] < 1) disabled @endif
                    >
                        <i class="bi bi-bag-heart" aria-hidden="true"></i>
                        Move All to Bag
                    </button>
                </div>
            </div>

            @if (session('success'))
                <div class="wishlist-flash wishlist-flash--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="wishlist-flash wishlist-flash--error">{{ session('error') }}</div>
            @endif

            @if ($wishlist['count'] < 1)
                <div class="wishlist-empty">
                    <h2 class="font-heading">Your wishlist is empty</h2>
                    <p>Save pieces you love and find them here anytime.</p>
                    <a href="{{ route('products.new-arrivals') }}" class="wishlist-btn wishlist-btn--primary">Continue Shopping</a>
                </div>
            @else
                <div class="wishlist-grid" data-wishlist-grid>
                    @foreach ($wishlist['items'] as $item)
                        @include('frontend.components.wishlist.card', ['item' => $item])
                    @endforeach
                </div>
            @endif
        </div>

        <section class="wishlist-trust" aria-label="Shopping benefits">
            <div class="wishlist-trust__inner">
                @foreach ($trustItems as $trust)
                    <div class="wishlist-trust__item">
                        <i class="bi {{ $trust['icon'] }}" aria-hidden="true"></i>
                        <div>
                            <strong>{{ $trust['title'] }}</strong>
                            <span>{{ $trust['subtitle'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
