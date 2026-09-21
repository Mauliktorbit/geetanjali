@extends('frontend.layouts.app')

@php
    $listingUrl = $listingUrl ?? route('collections.bridal');
    $canonicalUrl = $canonicalUrl ?? url('/bridal-collection');
    $pageTitle = $pageTitle ?? 'Bridal Jewellery Collection | Geetanjali Jewellers';
    $metaDescription = $metaDescription ?? "Explore Geetanjali Jewellers' bridal jewellery collection featuring Kundan bridal sets, gold necklaces, earrings, bangles, rings and traditional wedding jewellery.";
    $introHeading = $introHeading ?? 'Bridal Jewellery Collection';
    $introText = $introText ?? 'Discover our stunning range of bridal jewellery including necklaces, earrings, bangles, rings and complete bridal sets.';
    $emptyMessage = $emptyMessage ?? 'No bridal products match your filters.';
    $showBridalSets = $showBridalSets ?? true;
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)

@push('styles')
    <link rel="canonical" href="{{ $canonicalUrl }}">
@endpush

@section('content')
    <div class="bridal-page" data-bridal-page data-view="{{ $viewMode ?? ($filters['view'] ?? 'grid') }}">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.collection.bridal-hero', ['hero' => $hero])

        <section class="bridal-intro" aria-labelledby="bridal-intro-heading">
            <div class="site-container">
                <h2 id="bridal-intro-heading" class="font-heading">{{ $introHeading }}</h2>
                @include('frontend.components.gold-divider', ['align' => 'center'])
                <p>{{ $introText }}</p>
            </div>
        </section>

        <section class="bridal-listing" id="collection-products" aria-label="{{ $introHeading }} products">
            <div class="site-container">
                @include('frontend.components.collection.product-listing', [
                    'listingUrl' => $listingUrl,
                    'filters' => $filters,
                    'filterCounts' => $filterCounts ?? [],
                    'products' => $products,
                    'emptyMessage' => $emptyMessage,
                    'formPrefix' => 'bridal',
                    'showBridalSets' => $showBridalSets,
                ])
            </div>
        </section>

        @include('frontend.components.collection.bridal-promos', ['promos' => $promos])
    </div>
@endsection
