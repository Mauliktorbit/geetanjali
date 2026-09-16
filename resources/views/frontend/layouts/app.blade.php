<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('brand.name') . ' — ' . config('brand.tagline'))</title>
    <meta name="description" content="@yield('meta_description', config('brand.business_type'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('frontend.components.asset-head')
    @stack('styles')
</head>
<body>
    @include('frontend.components.page-loader')
    @include('frontend.components.navbar')

    <main id="main-content">
        @include('frontend.components.flash')
        @yield('content')
    </main>

    @include('frontend.components.delivery-review-modal')
    @include('frontend.components.quick-view')

    @hasSection('newsletter')
        @yield('newsletter')
    @else
        @include('frontend.components.newsletter')
    @endif
    @include('frontend.components.footer')

    @include('frontend.components.asset-scripts')
    @stack('scripts')
</body>
</html>
