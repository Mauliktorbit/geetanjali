<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('brand.name'))</title>
    <meta name="description" content="@yield('meta_description', config('brand.business_type'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @include('frontend.components.asset-head')
    @stack('styles')
</head>
<body class="auth-body">
    @include('frontend.components.page-loader')
    <div class="auth-shell">
        <div class="auth-card">
            @include('auth.components.header')

            <div class="auth-body-area">
                @yield('content')
            </div>

            @include('auth.components.benefits')
            @include('auth.components.security-bar')
        </div>
    </div>
    @include('frontend.components.asset-scripts')
    @stack('scripts')
</body>
</html>
