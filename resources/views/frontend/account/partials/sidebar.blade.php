@php
    $nav = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid', 'route' => 'account.index'],
        ['key' => 'orders', 'label' => 'My Orders', 'icon' => 'bi-bag-check', 'route' => 'account.orders'],
        ['key' => 'wishlist', 'label' => 'Wishlist', 'icon' => 'bi-heart', 'url' => route('wishlist.index')],
        ['key' => 'addresses', 'label' => 'Addresses', 'icon' => 'bi-geo-alt', 'route' => 'account.addresses'],
        ['key' => 'profile', 'label' => 'Account Details', 'icon' => 'bi-person', 'route' => 'account.profile'],
        ['key' => 'payments', 'label' => 'Payment Methods', 'icon' => 'bi-credit-card', 'route' => 'account.payments'],
        ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'account.notifications'],
        ['key' => 'coupons', 'label' => 'My Coupons', 'icon' => 'bi-ticket-perforated', 'route' => 'account.coupons'],
        ['key' => 'reviews', 'label' => 'My Reviews', 'icon' => 'bi-star', 'route' => 'account.reviews'],
        ['key' => 'returns', 'label' => 'Returns & Refunds', 'icon' => 'bi-arrow-return-left', 'route' => 'account.returns'],
        ['key' => 'help', 'label' => 'Help & Support', 'icon' => 'bi-question-circle', 'route' => 'account.help'],
    ];
    $current = collect($nav)->firstWhere('key', $accountSection ?? 'dashboard') ?? $nav[0];
@endphp

<aside class="account-sidebar">
    <div class="account-nav-box">
        <input type="checkbox" id="account-nav-toggle" class="account-nav-check" autocomplete="off">
        <label for="account-nav-toggle" class="account-nav-toggle">
            <span class="account-nav-toggle__current">
                <i class="bi {{ $current['icon'] }}" aria-hidden="true"></i>
                {{ $current['label'] }}
            </span>
            <span class="account-nav-toggle__action">
                Menu
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </span>
        </label>

        <nav class="account-nav" aria-label="Account">
            @foreach ($nav as $item)
                @php
                    $href = $item['url'] ?? route($item['route']);
                    $active = ($accountSection ?? '') === $item['key'];
                @endphp
                <a href="{{ $href }}" class="account-nav__link{{ $active ? ' is-active' : '' }}">
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <form method="POST" action="{{ route('logout') }}" class="account-nav__logout" data-confirm="Log out? You will need to sign in again to view your account.">
                @csrf
                <button type="submit" class="account-nav__link" data-confirm="Log out? You will need to sign in again to view your account.">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    Logout
                </button>
            </form>
        </nav>
    </div>

    <div class="account-help">
        <span class="account-help__icon" aria-hidden="true"><i class="bi bi-headset"></i></span>
        <h3>Need Help?</h3>
        <p>Our jewellery consultants are here for sizing, care, and order support.</p>
        <a href="{{ route('contact') }}" class="account-help__btn">Contact Support</a>
    </div>
</aside>
