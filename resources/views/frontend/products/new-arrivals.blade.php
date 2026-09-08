@extends('frontend.layouts.app')

@section('title', 'New Arrivals | Geetanjali Jewellers')
@section('meta_description', 'Discover the latest jewellery arrivals from Geetanjali Jewellers, featuring elegant gold, diamond, kundan and bridal designs.')

@push('styles')
    <link rel="canonical" href="{{ url('/new-arrivals') }}">
@endpush

@section('content')
    <div class="new-arrivals-page" data-new-arrivals-page data-view="{{ $viewMode }}">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.new-arrivals.hero', ['hero' => $hero])

        <section class="na-listing" aria-label="New arrival products">
            <div class="na-container">
                @include('frontend.components.new-arrivals.toolbar', [
                    'filters' => $filters,
                    'products' => $products,
                ])

                @if ($products->count() > 0)
                    <div class="na-grid {{ $viewMode === 'list' ? 'na-grid--list' : '' }}" data-na-grid>
                        @foreach ($products as $product)
                            @include('frontend.components.product-card', [
                                'product' => $product,
                                'showCart' => false,
                            ])
                        @endforeach
                    </div>

                    <div class="na-pagination">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="na-empty">
                        <h2 class="font-heading">No new arrivals found</h2>
                        <p>Please check back soon for our latest jewellery collections.</p>
                        <a href="{{ route('home') }}" class="na-btn">Continue Shopping</a>
                    </div>
                @endif
            </div>
        </section>

        @include('frontend.components.new-arrivals.service-strip', ['services' => $services])
    </div>

    <div class="na-filter-drawer" id="naFilterDrawer" data-na-drawer hidden>
        <div class="na-filter-drawer__backdrop" data-na-drawer-close tabindex="-1"></div>
        <div class="na-filter-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="na-drawer-title">
            <div class="na-filter-drawer__head">
                <h2 id="na-drawer-title" class="font-heading">Filters</h2>
                <button type="button" class="na-filter-drawer__close" data-na-drawer-close aria-label="Close filters">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="na-filter-drawer__body">
                @include('frontend.components.new-arrivals.filter-panel', [
                    'filters' => $filters,
                    'formId' => 'na-filter-form',
                ])
            </div>
        </div>
    </div>
@endsection
