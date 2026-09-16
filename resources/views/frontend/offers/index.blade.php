@extends('frontend.layouts.app')

@section('title', 'Offers | Geetanjali Jewellers')
@section('meta_description', 'Explore exclusive gold, diamond and kundan jewellery offers from Geetanjali Jewellers.')

@push('styles')
    <link rel="canonical" href="{{ url('/offers') }}">
@endpush

@section('content')
    <div class="offers-page" data-offers-page>
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.offers.hero', ['hero' => $hero])

        @include('frontend.components.offers.benefits', ['benefits' => $benefits])

        <section class="offers-section" aria-labelledby="offers-heading">
            <div class="offers-container">
                <h2 id="offers-heading" class="font-heading">Best Offers For You</h2>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (! empty($filterTabs) && count($filterTabs) > 1)
                    @include('frontend.components.offers.filters', [
                        'tabs' => $filterTabs,
                        'active' => $activeCategory,
                    ])
                @endif

                @if ($offers->count() > 0)
                    <div class="offers-grid">
                        @foreach ($offers as $offer)
                            @include('frontend.components.offers.offer-card', ['offer' => $offer])
                        @endforeach
                    </div>
                @else
                    <div class="offers-empty">
                        <p>No coupons are running right now. Explore our collections below.</p>
                    </div>
                @endif
            </div>
        </section>

        @if (! empty($highlights))
            <section class="offers-section offers-section--highlights" aria-labelledby="offers-highlights-heading">
                <div class="offers-container">
                    <h2 id="offers-highlights-heading" class="font-heading">Explore Geetanjali</h2>
                    @include('frontend.components.gold-divider', ['align' => 'center'])
                    <div class="offers-highlights">
                        @foreach ($highlights as $item)
                            <article class="offers-highlight">
                                <div class="offers-highlight__media">
                                    <img src="{{ storefront_image($item['image'] ?? null) }}" alt="{{ $item['title'] }}" width="400" height="260" loading="lazy">
                                </div>
                                <div class="offers-highlight__body">
                                    <h3 class="font-heading">{{ $item['title'] }}</h3>
                                    <p>{{ $item['text'] }}</p>
                                    <a href="{{ $item['url'] }}" class="offers-join-btn">{{ $item['cta'] }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @include('frontend.components.offers.terms', ['terms' => $terms])
    </div>
@endsection
