<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') — {{ config('app.name', 'Commerce Admin') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>
<div class="guest-shell">
    <div class="guest-card">
        <div class="guest-brand">
            <div class="guest-brand-mark">C</div>
            <h1>{{ config('app.name', 'Commerce') }}</h1>
            <p>@yield('subtitle', 'Sign in to your admin account')</p>
        </div>

        @include('admin.components.alerts')

        @yield('content')

        <div class="guest-footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Commerce Admin') }}. All rights reserved.
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
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
