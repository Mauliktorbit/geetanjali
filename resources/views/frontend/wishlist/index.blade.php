@extends('frontend.layouts.app')

@php
    $isShared = $isShared ?? false;
    $shareOwner = $shareOwner ?? null;
    $heading = $isShared
        ? (($shareOwner ?: 'Shared').' Wishlist')
        : 'My Wishlist';
    $emptyTitle = $isShared ? 'This shared wishlist is empty' : 'Your wishlist is empty';
    $emptyCopy = $isShared
        ? 'The owner has not saved any pieces yet.'
        : 'Save pieces you love and find them here anytime.';
@endphp

@section('title', $heading.' | Geetanjali Jewellers')
@section('meta_description', $isShared
    ? 'A shared Geetanjali Jewellers wishlist.'
    : 'Your favourite Geetanjali Jewellers pieces, saved in one place.')

@section('content')
    <div
        class="wishlist-page"
        data-wishlist-page
        data-csrf="{{ csrf_token() }}"
        @if (! $isShared)
            data-move-all-url="{{ route('wishlist.move-all') }}"
            data-share-endpoint="{{ route('wishlist.share') }}"
        @endif
        @if ($isShared) data-shared="1" @endif
    >
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="wishlist-shell">
            <div class="wishlist-header">
                <div class="wishlist-header__copy">
                    <h1 class="font-heading">
                        {{ $heading }}
                        <span class="wishlist-header__count" data-wishlist-count>({{ $wishlist['count'] }})</span>
                    </h1>
                    @include('frontend.components.gold-divider', ['align' => 'left'])
                    <p class="wishlist-header__sub">
                        @if ($isShared)
                            Pieces saved by {{ $shareOwner ?: 'a Geetanjali customer' }}.
                        @else
                            Your favourite pieces, all in one place. Don't miss out!
                        @endif
                    </p>
                </div>

                @if (! $isShared)
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
                @endif
            </div>

            @if (session('success'))
                <div class="wishlist-flash wishlist-flash--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="wishlist-flash wishlist-flash--error">{{ session('error') }}</div>
            @endif

            @if ($wishlist['count'] < 1)
                <div class="wishlist-empty">
                    <h2 class="font-heading">{{ $emptyTitle }}</h2>
                    <p>{{ $emptyCopy }}</p>
                    <a href="{{ route('products.new-arrivals') }}" class="wishlist-btn wishlist-btn--primary">Continue Shopping</a>
                </div>
            @else
                <div class="wishlist-grid" data-wishlist-grid>
                    @foreach ($wishlist['items'] as $item)
                        @include('frontend.components.wishlist.card', [
                            'item' => $item,
                            'readonly' => $isShared,
                        ])
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
