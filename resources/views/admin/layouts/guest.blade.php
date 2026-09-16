<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') — {{ config('brand.name', config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @include('frontend.components.favicon')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body>
<div class="guest-shell">
    <div class="guest-card">
        <div class="guest-brand">
            <img
                class="guest-brand-logo"
                src="{{ asset(config('brand.logo')) }}"
                alt="{{ config('brand.name') }}"
                width="220"
                height="56"
            >
            <h1>Admin Panel</h1>
            <p>@yield('subtitle', 'Sign in to your admin account')</p>
        </div>

        @include('admin.components.alerts')

        @yield('content')

        <div class="guest-footer">
            &copy; {{ date('Y') }} {{ config('brand.name', config('app.name')) }}. All rights reserved.
        </div>
    </div>
</div>

<script id="admin-flash-data" type="application/json">
@php
    $flashPayload = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
        'status' => session('status'),
        'errors' => isset($errors) && $errors->any() ? $errors->all() : null,
    ];
@endphp
{!! json_encode(array_filter($flashPayload)) !!}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/sweet-alerts.js') }}?v={{ filemtime(public_path('js/sweet-alerts.js')) }}"></script>
<script src="{{ asset('js/input-masks.js') }}?v={{ filemtime(public_path('js/input-masks.js')) }}"></script>
<script src="{{ asset('js/input-filled.js') }}?v={{ filemtime(public_path('js/input-filled.js')) }}"></script>
<script src="{{ asset('js/password-toggle.js') }}?v={{ filemtime(public_path('js/password-toggle.js')) }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
