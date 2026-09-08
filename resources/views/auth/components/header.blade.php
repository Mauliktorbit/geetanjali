<header class="auth-header">
    <a href="{{ route('home') }}" class="auth-logo" aria-label="{{ config('brand.name') }} Home">
        <img
            src="{{ asset(config('brand.logo')) }}"
            alt="{{ config('brand.name') }}"
            width="260"
            height="70"
        >
    </a>

    <a href="{{ route('home') }}" class="auth-home-link">
        <i class="bi bi-house" aria-hidden="true"></i>
        Back to Home
    </a>
</header>
