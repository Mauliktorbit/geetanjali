@extends('frontend.layouts.app')

@section('title', config('brand.name') . ' — Premium Kundan, Gold & Diamond Jewellery')
@section('meta_description', 'Discover exclusive Kundan, Gold and Diamond jewellery from Geetanjali Jewellers. Crafted with heritage, made for modern elegance.')

@section('content')
    @include('frontend.components.hero', ['slides' => $heroSlides])

    @include('frontend.components.category-section', ['categories' => $categories])

    @include('frontend.components.service-strip', ['services' => $services])

    @include('frontend.components.best-sellers', [
        'tabs' => $bestSellerTabs,
        'productsByTab' => $productsByTab,
    ])

    @include('frontend.components.occasion-section', ['occasions' => $occasions])

    @include('frontend.components.kundan-banner', ['banner' => $kundanBanner])

    @include('frontend.components.why-choose', ['features' => $whyFeatures])

    @include('frontend.components.testimonials', ['testimonials' => $testimonials])

    @include('frontend.components.instagram-gallery', ['images' => $galleryImages])
@endsection
