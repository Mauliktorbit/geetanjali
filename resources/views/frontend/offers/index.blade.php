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

                @include('frontend.components.offers.filters', [
                    'tabs' => $filterTabs,
                    'active' => $activeCategory,
                ])

                @if ($offers->count() > 0)
                    <div class="offers-grid">
                        @foreach ($offers as $offer)
                            @include('frontend.components.offers.offer-card', ['offer' => $offer])
                        @endforeach
                    </div>
                @else
                    <div class="offers-empty">
                        <p>No offers available in this category right now.</p>
                        <a href="{{ route('offers.index') }}" class="offers-join-btn">View All Offers</a>
                    </div>
                @endif
            </div>
        </section>

        @include('frontend.components.offers.gold-club', ['goldClub' => $goldClub])

        @include('frontend.components.offers.terms', ['terms' => $terms])
    </div>
@endsection
