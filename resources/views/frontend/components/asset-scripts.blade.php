<script>
window.Geetanjali = Object.assign(window.Geetanjali || {}, {
    loggedIn: @json(auth()->check() && ! auth()->user()?->is_staff),
    routes: {
        login: @json(route('login')),
        cart: @json(route('cart.index')),
        cartAdd: @json(route('cart.add')),
        wishlistToggle: @json(\Illuminate\Support\Facades\Route::has('wishlist.toggle') ? route('wishlist.toggle') : ''),
        checkout: @json(route('checkout.index')),
    },
});
</script>
<script src="{{ asset('public/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/sweet-alerts.js') }}?v={{ filemtime(public_path('js/sweet-alerts.js')) }}"></script>
<script src="{{ asset('js/input-masks.js') }}?v={{ filemtime(public_path('js/input-masks.js')) }}"></script>
<script src="{{ asset('js/password-toggle.js') }}?v={{ filemtime(public_path('js/password-toggle.js')) }}"></script>
<script src="{{ asset('public/assets/js/app.js') }}"></script>
<script src="{{ asset('public/assets/js/storefront.js') }}?v=20260910a"></script>
<script src="{{ asset('public/assets/js/home.js') }}"></script>
<script src="{{ asset('public/assets/js/product.js') }}?v=20260910a"></script>
<script src="{{ asset('public/assets/js/collection.js') }}"></script>
<script src="{{ asset('public/assets/js/bridal.js') }}"></script>
<script src="{{ asset('public/assets/js/new-arrivals.js') }}"></script>
<script src="{{ asset('public/assets/js/offers.js') }}"></script>
<script src="{{ asset('public/assets/js/auth.js') }}"></script>
<script src="{{ asset('public/assets/js/cart.js') }}?v=20260909a"></script>
<script src="{{ asset('public/assets/js/checkout.js') }}?v=20260827d"></script>
<script src="{{ asset('public/assets/js/wishlist.js') }}"></script>
