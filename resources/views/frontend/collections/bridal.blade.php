@extends('frontend.layouts.app')

@section('title', 'Bridal Jewellery Collection | Geetanjali Jewellers')
@section('meta_description', "Explore Geetanjali Jewellers' bridal jewellery collection featuring Kundan bridal sets, gold necklaces, earrings, bangles, rings and traditional wedding jewellery.")

@push('styles')
    <link rel="canonical" href="{{ url('/bridal-collection') }}">
@endpush

@section('content')
    <div class="bridal-page" data-bridal-page data-view="{{ $viewMode }}">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.collection.bridal-hero', ['hero' => $hero])

        @include('frontend.components.collection.bridal-service-strip', [
            'services' => $topServices,
            'variant' => 'top',
        ])

        <section class="bridal-intro" aria-labelledby="bridal-intro-heading">
            <div class="site-container">
                <h2 id="bridal-intro-heading" class="font-heading">Bridal Jewellery Collection</h2>
                @include('frontend.components.gold-divider', ['align' => 'center'])
                <p>Discover our stunning range of bridal jewellery including necklaces, earrings, bangles, rings and complete bridal sets.</p>
            </div>
        </section>

        <section class="bridal-listing" id="bridal-products" aria-label="Bridal products">
            <div class="site-container">
                @include('frontend.components.collection.bridal-toolbar', ['filters' => $filters])

                <div class="bridal-listing__meta">
                    <p class="bridal-listing__count">
                        @if ($products->total() > 0)
                            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} Products
                        @else
                            Showing 0 Products
                        @endif
                    </p>
                </div>

                @if ($products->count() > 0)
                    <div class="bridal-grid {{ $viewMode === 'list' ? 'bridal-grid--list' : '' }}" data-bridal-grid>
                        @foreach ($products as $product)
                            @include('frontend.components.product-card', [
                                'product' => $product,
                                'showCart' => false,
                            ])
                        @endforeach
                    </div>

                    <div class="bridal-pagination">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="bridal-empty">
                        <p>No bridal products match your filters.</p>
                        <a href="{{ route('collections.bridal') }}" class="bridal-btn">Clear Filters</a>
                    </div>
                @endif
            </div>
        </section>

        @include('frontend.components.collection.bridal-promos', ['promos' => $promos])

        @include('frontend.components.collection.bridal-service-strip', [
            'services' => $trustServices,
            'variant' => 'trust',
        ])
    </div>

    {{-- Mobile filter drawer --}}
    <div class="bridal-filter-drawer" id="bridalFilterDrawer" data-bridal-drawer hidden>
        <div class="bridal-filter-drawer__backdrop" data-bridal-drawer-close tabindex="-1"></div>
        <div class="bridal-filter-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="bridal-drawer-title">
            <div class="bridal-filter-drawer__head">
                <h2 id="bridal-drawer-title">Filters</h2>
                <button type="button" class="bridal-filter-drawer__close" data-bridal-drawer-close aria-label="Close filters">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="bridal-filter-drawer__body">
                @include('frontend.components.collection.bridal-filter-form', [
                    'filters' => $filters,
                    'formId' => 'bridal-filter-form-mobile',
                    'showSort' => true,
                    'compact' => false,
                ])
            </div>
        </div>
    </div>
@endsection
