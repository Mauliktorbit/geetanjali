@extends('frontend.layouts.app')

@section('title', 'About Us — ' . config('brand.name'))
@section('meta_description', 'Discover the legacy of Geetanjali Jewellers — trusted Kundan, Gold and Diamond craftsmanship rooted in tradition and modern elegance.')

@section('content')
    <div class="about-page">
        @include('frontend.components.breadcrumb', [
            'items' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'About Us', 'url' => null],
            ],
        ])

        @include('frontend.components.about-hero', $hero)

        @include('frontend.components.story-section', $story)

        @include('frontend.components.values-section', ['values' => $values])

        @include('frontend.components.statistics-section', ['stats' => $stats])

        @include('frontend.components.promise-section', $promise)
    </div>
@endsection

@section('newsletter')
    @include('frontend.components.about-newsletter')
@endsection
