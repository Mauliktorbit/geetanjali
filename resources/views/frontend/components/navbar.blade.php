@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Kundan Collection', 'route' => 'collections.kundan'],
        ['label' => 'Bridal Collection', 'route' => 'collections.bridal'],
        ['label' => 'New Arrivals', 'route' => 'products.new-arrivals'],
        ['label' => 'Offers', 'route' => 'offers.index'],
        ['label' => 'About Us', 'route' => 'about'],
        ['label' => 'Contact Us', 'route' => 'contact'],
    ];
@endphp

<div class="top-promo">
    <div class="site-container top-promo__inner">
        <p class="top-promo__message">{{ config('brand.promo.message') }}</p>
        <ul class="top-promo__links" aria-label="Quick links">
            <li><a href="{{ route('pages.store-locator') }}">Store Locator</a></li>
            <li><a href="{{ route('pages.track-order') }}">Track Order</a></li>
            <li><a href="{{ route('pages.help') }}">Help</a></li>
        </ul>
    </div>
</div>

<header class="site-header" data-site-header>
    <div class="site-container main-navbar">
        <button
            class="mobile-menu-toggle"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileNav"
            aria-controls="mobileNav"
            aria-label="Open menu"
        >
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <a href="{{ route('home') }}" class="brand-logo" aria-label="{{ config('brand.name') }} Home">
            <img
                src="{{ asset(config('brand.logo')) }}"
                alt="{{ config('brand.name') }} logo"
                width="190"
                height="52"
            >
        </a>

        <form class="navbar-search" action="{{ route('products.new-arrivals') }}" method="get" role="search">
            <label class="visually-hidden" for="site-search">Search jewellery</label>
            <input
                id="site-search"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search for exquisite jewellery..."
                autocomplete="off"
            >
            <button type="submit" aria-label="Search">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>
        </form>

        <div class="navbar-actions">
            <a href="{{ route('wishlist.index') }}" class="nav-action nav-action--wishlist" aria-label="Wishlist">
                <i class="bi bi-heart" aria-hidden="true"></i>
                <span>Wishlist</span>
                <span class="cart-badge" data-wishlist-badge aria-label="{{ $wishlistCount ?? 0 }} items in wishlist">{{ $wishlistCount ?? 0 }}</span>
            </a>
            @auth
                @if (auth()->user()->is_staff)
                    <a href="{{ route('admin.dashboard') }}" class="nav-action nav-action--login" aria-label="Admin Panel">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <span>Admin Panel</span>
                    </a>
                @else
                    <a href="{{ route('account.index') }}" class="nav-action nav-action--login{{ request()->routeIs('account.*') ? ' is-current' : '' }}" aria-label="My Account">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <span>My Account</span>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="nav-action nav-action--login" aria-label="Login or Register">
                    <i class="bi bi-person" aria-hidden="true"></i>
                    <span>Login / Register</span>
                </a>
            @endauth
            <a href="{{ route('cart.index') }}" class="nav-action" aria-label="Shopping cart">
                <i class="bi bi-bag" aria-hidden="true"></i>
                <span>Cart</span>
                <span class="cart-badge" data-cart-badge aria-label="{{ $cartCount ?? 0 }} items in cart">{{ $cartCount ?? 0 }}</span>
            </a>
        </div>
    </div>

    <div class="mobile-search">
        <form class="navbar-search" action="{{ route('products.new-arrivals') }}" method="get" role="search">
            <label class="visually-hidden" for="mobile-search">Search jewellery</label>
            <input
                id="mobile-search"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search for exquisite jewellery..."
                autocomplete="off"
            >
            <button type="submit" aria-label="Search">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>
        </form>
    </div>

    <nav class="primary-nav" aria-label="Primary">
        <div class="site-container">
            <ul class="primary-nav__list">
                @foreach ($navItems as $item)
                    @if (!empty($item['dropdown']))
                        <li class="dropdown">
                            <button
                                class="dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                {{ $item['label'] }}
                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @foreach ($item['dropdown'] as $child)
                                    <li>
                                        <a class="dropdown-item" href="{{ $child['url'] ?? '#' }}">{{ $child['label'] ?? $child }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li>
                            @php
                                $isActive = isset($item['route']) && request()->routeIs($item['route']);
                                $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
                            @endphp
                            <a
                                href="{{ $href }}"
                                class="{{ $isActive ? 'is-active' : '' }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </nav>
</header>

<div class="offcanvas offcanvas-start offcanvas-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title mb-0" id="mobileNavLabel">
            <a href="{{ route('home') }}" class="offcanvas-nav__brand">
                <img
                    src="{{ asset(config('brand.logo')) }}"
                    alt="{{ config('brand.name') }}"
                    width="168"
                    height="46"
                >
            </a>
        </h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="offcanvas-nav__menu" aria-label="Mobile">
            @foreach ($navItems as $item)
                @php
                    $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
                    $isActive = isset($item['route']) && request()->routeIs($item['route']);
                @endphp
                <a class="nav-link{{ $isActive ? ' is-active' : '' }}" href="{{ $href }}">{{ $item['label'] }}</a>
            @endforeach
            <a class="nav-link" href="{{ route('wishlist.index') }}">Wishlist</a>
            @auth
                <a class="nav-link" href="{{ auth()->user()->is_staff ? route('admin.dashboard') : route('account.index') }}">
                    {{ auth()->user()->is_staff ? 'Admin Panel' : 'My Account' }}
                </a>
            @else
                <a class="nav-link" href="{{ route('login') }}">Login / Register</a>
            @endauth
            <a class="nav-link" href="{{ route('cart.index') }}">Cart</a>
            <a class="nav-link" href="{{ route('pages.store-locator') }}">Store Locator</a>
            <a class="nav-link" href="{{ route('pages.track-order') }}">Track Order</a>
            <a class="nav-link" href="{{ route('pages.help') }}">Help</a>
        </nav>
    </div>
</div>
