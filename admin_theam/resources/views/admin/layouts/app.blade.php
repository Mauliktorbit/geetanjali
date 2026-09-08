<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Commerce Admin') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
        <div class="sidebar-brand">
            <div class="sidebar-brand-mark">C</div>
            <div class="sidebar-brand-text">
                Commerce
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>

            {{-- Dashboard --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.dashboard') }}" class="nav-link {{ $isActive('admin.dashboard', 'admin.dashboard.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg></span>
                    <span class="nav-label">Dashboard</span>
                </a>
            </div>

            {{-- Orders --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.orders.index') }}" class="nav-link {{ $isActive('admin.orders.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M6 2h12l2 7H4L6 2z"/><path d="M4 9h16v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9z"/><path d="M9 13h6"/></svg></span>
                    <span class="nav-label">Orders</span>
                </a>
            </div>

            {{-- Products --}}
            <div class="nav-item {{ $isActive('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.tags.*', 'admin.tax-rates.*') ? 'open' : '' }}">
                <button type="button" class="nav-link {{ $isActive('admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*', 'admin.tags.*', 'admin.tax-rates.*') ? 'active' : '' }}" data-nav-toggle>
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg></span>
                    <span class="nav-label">Products</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <div class="nav-submenu">
                    <a href="{{ $adminRoute('admin.products.index') }}" class="nav-sublink {{ $isActive('admin.products.*') ? 'active' : '' }}">All Products</a>
                    <a href="{{ $adminRoute('admin.categories.index') }}" class="nav-sublink {{ $isActive('admin.categories.*') ? 'active' : '' }}">Categories</a>
                    <a href="{{ $adminRoute('admin.brands.index') }}" class="nav-sublink {{ $isActive('admin.brands.*') ? 'active' : '' }}">Brands</a>
                    <a href="{{ $adminRoute('admin.attributes.index') }}" class="nav-sublink {{ $isActive('admin.attributes.*') ? 'active' : '' }}">Attributes</a>
                    <a href="{{ $adminRoute('admin.tags.index') }}" class="nav-sublink {{ $isActive('admin.tags.*') ? 'active' : '' }}">Tags</a>
                    <a href="{{ $adminRoute('admin.tax-rates.index') }}" class="nav-sublink {{ $isActive('admin.tax-rates.*') ? 'active' : '' }}">Tax Rates</a>
                </div>
            </div>

            {{-- Inventory --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.inventory.index') }}" class="nav-link {{ $isActive('admin.inventory.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M12 12v4"/><path d="M10 14h4"/></svg></span>
                    <span class="nav-label">Inventory</span>
                </a>
            </div>

            {{-- Customers --}}
            <div class="nav-item {{ $isActive('admin.customers.*', 'admin.customer-groups.*', 'admin.abandoned-carts.*') ? 'open' : '' }}">
                <button type="button" class="nav-link {{ $isActive('admin.customers.*', 'admin.customer-groups.*', 'admin.abandoned-carts.*') ? 'active' : '' }}" data-nav-toggle>
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                    <span class="nav-label">Customers</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <div class="nav-submenu">
                    <a href="{{ $adminRoute('admin.customers.index') }}" class="nav-sublink {{ $isActive('admin.customers.*') ? 'active' : '' }}">Customers</a>
                    <a href="{{ $adminRoute('admin.customer-groups.index') }}" class="nav-sublink {{ $isActive('admin.customer-groups.*') ? 'active' : '' }}">Groups</a>
                    <a href="{{ $adminRoute('admin.abandoned-carts.index') }}" class="nav-sublink {{ $isActive('admin.abandoned-carts.*') ? 'active' : '' }}">Abandoned Carts</a>
                </div>
            </div>

            <div class="nav-section-label">Commerce</div>

            {{-- Payments --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.payments.index') }}" class="nav-link {{ $isActive('admin.payments.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
                    <span class="nav-label">Payments</span>
                </a>
            </div>

            {{-- Shipping --}}
            <div class="nav-item {{ $isActive('admin.shipping-zones.*', 'admin.shipping-methods.*', 'admin.couriers.*', 'admin.shipments.*') ? 'open' : '' }}">
                <button type="button" class="nav-link {{ $isActive('admin.shipping-zones.*', 'admin.shipping-methods.*', 'admin.couriers.*', 'admin.shipments.*') ? 'active' : '' }}" data-nav-toggle>
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>
                    <span class="nav-label">Shipping</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <div class="nav-submenu">
                    <a href="{{ $adminRoute('admin.shipping-zones.index') }}" class="nav-sublink {{ $isActive('admin.shipping-zones.*') ? 'active' : '' }}">Zones</a>
                    <a href="{{ $adminRoute('admin.shipping-methods.index') }}" class="nav-sublink {{ $isActive('admin.shipping-methods.*') ? 'active' : '' }}">Methods</a>
                    <a href="{{ $adminRoute('admin.couriers.index') }}" class="nav-sublink {{ $isActive('admin.couriers.*') ? 'active' : '' }}">Couriers</a>
                    <a href="{{ $adminRoute('admin.shipments.index') }}" class="nav-sublink {{ $isActive('admin.shipments.*') ? 'active' : '' }}">Shipments</a>
                </div>
            </div>

            {{-- Returns & Refunds --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.returns.index') }}" class="nav-link {{ $isActive('admin.returns.*', 'admin.refunds.*', 'admin.return-reasons.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg></span>
                    <span class="nav-label">Returns &amp; Refunds</span>
                </a>
            </div>

            {{-- Coupons & Offers --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.coupons.index') }}" class="nav-link {{ $isActive('admin.coupons.*', 'admin.offers.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5"/></svg></span>
                    <span class="nav-label">Coupons &amp; Offers</span>
                </a>
            </div>

            {{-- Marketing --}}
            <div class="nav-item {{ $isActive('admin.banners.*', 'admin.campaigns.*', 'admin.flash-sales.*', 'admin.affiliates.*', 'admin.gift-cards.*') ? 'open' : '' }}">
                <button type="button" class="nav-link {{ $isActive('admin.banners.*', 'admin.campaigns.*', 'admin.flash-sales.*', 'admin.affiliates.*', 'admin.gift-cards.*') ? 'active' : '' }}" data-nav-toggle>
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span>
                    <span class="nav-label">Marketing</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <div class="nav-submenu">
                    <a href="{{ $adminRoute('admin.banners.index') }}" class="nav-sublink {{ $isActive('admin.banners.*') ? 'active' : '' }}">Banners</a>
                    <a href="{{ $adminRoute('admin.campaigns.index') }}" class="nav-sublink {{ $isActive('admin.campaigns.*') ? 'active' : '' }}">Campaigns</a>
                    <a href="{{ $adminRoute('admin.flash-sales.index') }}" class="nav-sublink {{ $isActive('admin.flash-sales.*') ? 'active' : '' }}">Flash Sales</a>
                    <a href="{{ $adminRoute('admin.affiliates.index') }}" class="nav-sublink {{ $isActive('admin.affiliates.*') ? 'active' : '' }}">Affiliates</a>
                    <a href="{{ $adminRoute('admin.gift-cards.index') }}" class="nav-sublink {{ $isActive('admin.gift-cards.*') ? 'active' : '' }}">Gift Cards</a>
                </div>
            </div>

            {{-- Reviews & Q&A --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.reviews.index') }}" class="nav-link {{ $isActive('admin.reviews.*', 'admin.questions.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                    <span class="nav-label">Reviews &amp; Q&amp;A</span>
                </a>
            </div>

            <div class="nav-section-label">Operations</div>

            {{-- Suppliers & Purchases --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.suppliers.index') }}" class="nav-link {{ $isActive('admin.suppliers.*', 'admin.purchases.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg></span>
                    <span class="nav-label">Suppliers &amp; Purchases</span>
                </a>
            </div>

            {{-- Warehouses --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.warehouses.index') }}" class="nav-link {{ $isActive('admin.warehouses.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg></span>
                    <span class="nav-label">Warehouses</span>
                </a>
            </div>

            {{-- Website Content --}}
            <div class="nav-item {{ $isActive('admin.pages.*', 'admin.banner-sections.*', 'admin.blogs.*', 'admin.faqs.*', 'admin.testimonials.*', 'admin.menus.*', 'admin.store-locations.*', 'admin.homepage-sections.*') ? 'open' : '' }}">
                <button type="button" class="nav-link {{ $isActive('admin.pages.*', 'admin.banner-sections.*', 'admin.blogs.*', 'admin.faqs.*', 'admin.testimonials.*', 'admin.menus.*', 'admin.store-locations.*', 'admin.homepage-sections.*') ? 'active' : '' }}" data-nav-toggle>
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg></span>
                    <span class="nav-label">Website Content</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <div class="nav-submenu">
                    <a href="{{ $adminRoute('admin.pages.index') }}" class="nav-sublink {{ $isActive('admin.pages.*') ? 'active' : '' }}">Pages</a>
                    <a href="{{ $adminRoute('admin.banner-sections.index') }}" class="nav-sublink {{ $isActive('admin.banner-sections.*') ? 'active' : '' }}">Banners sections</a>
                    <a href="{{ $adminRoute('admin.blogs.index') }}" class="nav-sublink {{ $isActive('admin.blogs.*') ? 'active' : '' }}">Blogs</a>
                    <a href="{{ $adminRoute('admin.faqs.index') }}" class="nav-sublink {{ $isActive('admin.faqs.*') ? 'active' : '' }}">FAQs</a>
                    <a href="{{ $adminRoute('admin.testimonials.index') }}" class="nav-sublink {{ $isActive('admin.testimonials.*') ? 'active' : '' }}">Testimonials</a>
                    <a href="{{ $adminRoute('admin.menus.index') }}" class="nav-sublink {{ $isActive('admin.menus.*') ? 'active' : '' }}">Menus</a>
                    <a href="{{ $adminRoute('admin.store-locations.index') }}" class="nav-sublink {{ $isActive('admin.store-locations.*') ? 'active' : '' }}">Store Locations</a>
                    <a href="{{ $adminRoute('admin.homepage-sections.index') }}" class="nav-sublink {{ $isActive('admin.homepage-sections.*') ? 'active' : '' }}">Homepage Sections</a>
                </div>
            </div>

            {{-- Reports --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.reports.index') }}" class="nav-link {{ $isActive('admin.reports.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 2 5-6"/></svg></span>
                    <span class="nav-label">Reports</span>
                </a>
            </div>

            {{-- Expenses --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.expenses.index') }}" class="nav-link {{ $isActive('admin.expenses.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                    <span class="nav-label">Expenses</span>
                </a>
            </div>

            <div class="nav-section-label">System</div>

            {{-- Support Tickets --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.support-tickets.index') }}" class="nav-link {{ $isActive('admin.support-tickets.*', 'admin.support-ticket-categories.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                    <span class="nav-label">Support Tickets</span>
                </a>
            </div>

            {{-- Notifications --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.notifications.index') }}" class="nav-link {{ $isActive('admin.notifications.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
                    <span class="nav-label">Notifications</span>
                </a>
            </div>

            {{-- Staff & Roles --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.staff.index') }}" class="nav-link {{ $isActive('admin.staff.*', 'admin.roles.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                    <span class="nav-label">Staff &amp; Roles</span>
                </a>
            </div>

            {{-- Integrations --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.integrations.index') }}" class="nav-link {{ $isActive('admin.integrations.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>
                    <span class="nav-label">Integrations</span>
                </a>
            </div>

            {{-- Settings --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.settings.index') }}" class="nav-link {{ $isActive('admin.settings.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                    <span class="nav-label">Settings</span>
                </a>
            </div>

            {{-- Audit Logs --}}
            <div class="nav-item">
                <a href="{{ $adminRoute('admin.audit-logs.index') }}" class="nav-link {{ $isActive('admin.audit-logs.*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg></span>
                    <span class="nav-label">Audit Logs</span>
                </a>
            </div>
        </nav>

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
                <input type="search" placeholder="Search orders, products, customers…" aria-label="Search">
            </div>

            <div class="topbar-right">
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
                            <span class="role">{{ $user->role ?? 'Administrator' }}</span>
                        </span>
                    </button>
                    <div class="dropdown-menu" data-dropdown-menu>
                        <a href="{{ $adminRoute('admin.settings.index') }}">Account settings</a>
                        <a href="{{ $adminRoute('admin.profile.edit', [], '#') }}">Profile</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ $adminRoute('logout', [], url('/logout')) }}" data-no-loading>
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
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
