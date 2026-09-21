@extends('frontend.layouts.app')

@section('title', ($collection->seo_title ?: $collection->name).' | Geetanjali Jewellers')
@section('meta_description', $collection->seo_description ?: ('Explore '.$collection->name.' jewellery from Geetanjali Jewellers.'))

@section('content')
    <div class="kundan-page" data-kundan-page data-listing-page>
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.collection.hero', ['hero' => $hero])

        <section class="bridal-listing" id="collection-products" aria-label="{{ $collection->name }} products">
            <div class="site-container">
                @include('frontend.components.collection.product-listing', [
                    'listingUrl' => $listingUrl,
                    'filters' => $filters,
                    'filterCounts' => $filterCounts ?? [],
                    'products' => $products,
                    'emptyMessage' => $emptyMessage,
                    'formPrefix' => 'collection',
                    'showBridalSets' => false,
                ])
            </div>
        </section>
    </div>
@endsection
