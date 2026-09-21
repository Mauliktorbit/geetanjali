@extends('frontend.layouts.app')

@section('title', 'Kundan Collection | Geetanjali Jewellers')
@section('meta_description', "Explore Geetanjali Jewellers' handcrafted Kundan jewellery collection featuring necklaces, earrings, bangles, rings, bridal sets and more.")

@push('styles')
    <link rel="canonical" href="{{ url('/kundan') }}">
@endpush

@section('content')
    <div class="kundan-page" data-kundan-page data-listing-page>
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.collection.hero', ['hero' => $hero])

        @include('frontend.components.collection.category-nav', ['categories' => $categoryNav])

        @include('frontend.components.collection.intro')

        <section class="bridal-listing" id="collection-products" aria-label="Kundan products">
            <div class="site-container">
                @include('frontend.components.collection.product-listing', [
                    'listingUrl' => route('collections.kundan'),
                    'filters' => $filters,
                    'filterCounts' => $filterCounts ?? [],
                    'products' => $products,
                    'emptyMessage' => 'No products match your filters.',
                    'formPrefix' => 'kundan',
                    'showBridalSets' => false,
                ])
            </div>
        </section>

        @include('frontend.components.collection.why-kundan', ['features' => $whyFeatures])
    </div>
@endsection
