@extends('frontend.layouts.app')

@section('title', 'New Arrivals | Geetanjali Jewellers')
@section('meta_description', 'Discover the latest jewellery arrivals from Geetanjali Jewellers, featuring elegant gold, diamond, kundan and bridal designs.')

@push('styles')
    <link rel="canonical" href="{{ url('/new-arrivals') }}">
@endpush

@section('content')
    <div class="new-arrivals-page" data-listing-page data-view="{{ $viewMode ?? ($filters['view'] ?? 'grid') }}">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.new-arrivals.hero', ['hero' => $hero])

        <section class="bridal-listing" id="collection-products" aria-label="New arrival products">
            <div class="site-container">
                @include('frontend.components.collection.product-listing', [
                    'listingUrl' => $listingUrl ?? route('products.new-arrivals'),
                    'filters' => $filters,
                    'filterCounts' => $filterCounts ?? [],
                    'products' => $products,
                    'emptyMessage' => 'No new arrivals match your filters.',
                    'formPrefix' => 'new-arrivals',
                    'showBridalSets' => false,
                ])
            </div>
        </section>

        @include('frontend.components.new-arrivals.service-strip', ['services' => $services])
    </div>
@endsection
