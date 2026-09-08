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
<script src="{{ asset('public/assets/js/app.js') }}"></script>
<script src="{{ asset('public/assets/js/storefront.js') }}?v=20260827e"></script>
<script src="{{ asset('public/assets/js/home.js') }}"></script>
<script src="{{ asset('public/assets/js/product.js') }}"></script>
<script src="{{ asset('public/assets/js/collection.js') }}"></script>
<script src="{{ asset('public/assets/js/bridal.js') }}"></script>
<script src="{{ asset('public/assets/js/new-arrivals.js') }}"></script>
<script src="{{ asset('public/assets/js/offers.js') }}"></script>
<script src="{{ asset('public/assets/js/auth.js') }}"></script>
<script src="{{ asset('public/assets/js/cart.js') }}"></script>
<script src="{{ asset('public/assets/js/checkout.js') }}?v=20260827d"></script>
<script src="{{ asset('public/assets/js/wishlist.js') }}"></script>
