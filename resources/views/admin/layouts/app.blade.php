<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('brand.name', config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('public/assets/images/logo/geetanjali-logo-header.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body>
@php
    $adminRoute = function (string $name, $params = [], string $fallback = '#') {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : $fallback;
    };
    $isActive = function (...$patterns) {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        return false;
    };
    $user = auth()->user();
    $userName = $user->name ?? 'Admin';
    $userInitials = collect(explode(' ', $userName))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
@endphp

<div class="admin-shell" id="admin-shell">
    <div class="sidebar-overlay" data-sidebar-overlay></div>

    {{-- Sidebar --}}
    <aside class="sidebar" id="admin-sidebar" aria-label="Admin navigation">
        <a href="{{ $adminRoute('admin.dashboard') }}" class="sidebar-brand">
            <img
                class="sidebar-brand-logo"
                src="{{ asset(config('brand.logo')) }}"
                alt="{{ config('brand.name') }}"
                width="168"
                height="42"
            >
            <div class="sidebar-brand-text">
                Admin Panel
            </div>
        </a>

        @include('admin.partials.sidebar-nav')

        <div class="sidebar-footer">
            <button type="button" class="sidebar-collapse-btn" data-sidebar-collapse aria-label="Collapse sidebar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                <span class="nav-label">Collapse</span>
            </button>
        </div>
    </aside>

    {{-- Main --}}
    <div class="admin-main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="topbar-toggle" data-mobile-menu aria-label="Open menu">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <div class="topbar-search">
                <span class="topbar-search-icon">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input type="search" placeholder="Search orders, products, customers..." aria-label="Search">
            </div>

            <div class="topbar-right">
                <a href="{{ route('home') }}" class="topbar-btn" title="View website" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
                <div class="user-dropdown" data-dropdown>
                    <button type="button" class="topbar-btn" data-dropdown-trigger aria-label="Notifications">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="topbar-badge">3</span>
                    </button>
                    <div class="dropdown-menu" data-dropdown-menu style="min-width:280px">
                        <div style="padding:0.5rem 0.7rem;font-weight:650;font-size:0.85rem;color:var(--text-primary)">Notifications</div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ $adminRoute('admin.notifications.index') }}">View all notifications</a>
                    </div>
                </div>

                <div class="user-dropdown" data-dropdown>
                    <button type="button" class="user-trigger" data-dropdown-trigger>
                        <span class="user-avatar">{{ strtoupper($userInitials ?: 'AD') }}</span>
                        <span class="user-meta">
                            <span class="name">{{ $userName }}</span>
                            <span class="role">{{ $user?->roles->first()?->name ?? 'Administrator' }}</span>
                        </span>
                    </button>
                    <div class="dropdown-menu" data-dropdown-menu>
                        <a href="{{ $adminRoute('admin.settings.index') }}">Account settings</a>
                        <a href="{{ $adminRoute('admin.profile.edit', [], '#') }}">Profile</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ $adminRoute('admin.logout') }}" data-no-loading>
                            @csrf
                            <button type="submit">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content">
            @hasSection('breadcrumbs')
                <div class="mb-2">
                    @yield('breadcrumbs')
                </div>
            @endif

            {{-- Flash message area (inline + toast source) --}}
            <div id="flash-messages" class="flash-area">
                @include('admin.components.alerts')
            </div>

            @yield('content')
        </main>
    </div>
</div>

@include('admin.components.confirm-modal')

<div class="page-loader" aria-hidden="true">
    <div class="spinner spinner-lg"></div>
</div>

<script id="admin-flash-data" type="application/json">
@php
    $flashPayload = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
        'status' => session('status'),
        'errors' => (isset($errors) && $errors->any()) ? $errors->all() : null,
    ];
@endphp
{!! json_encode(array_filter($flashPayload)) !!}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
@stack('scripts')
</body>
</html>
