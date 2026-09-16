<script>
window.Geetanjali = Object.assign(window.Geetanjali || {}, {
    loggedIn: @json(auth()->check() && ! auth()->user()?->is_staff),
    wishlistIds: @json($wishlistProductIds ?? []),
    routes: {
        login: @json(route('login')),
        cart: @json(route('cart.index')),
        cartAdd: @json(route('cart.add')),
        wishlistToggle: @json(route('wishlist.toggle')),
        checkout: @json(route('checkout.index')),
    },
    deliveryReview: @json($deliveryReviewPrompt ?? null),
});
</script>
<script src="{{ asset('public/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/sweet-alerts.js') }}?v={{ filemtime(public_path('js/sweet-alerts.js')) }}"></script>
<script src="{{ asset('js/input-masks.js') }}?v={{ filemtime(public_path('js/input-masks.js')) }}"></script>
<script src="{{ asset('js/input-filled.js') }}?v={{ filemtime(public_path('js/input-filled.js')) }}"></script>
<script src="{{ asset('js/password-toggle.js') }}?v={{ filemtime(public_path('js/password-toggle.js')) }}"></script>
<script src="{{ asset('js/delivery-review.js') }}?v={{ filemtime(public_path('js/delivery-review.js')) }}"></script>
<script src="{{ asset('public/assets/js/app.js') }}?v=20260916q"></script>
<script src="{{ asset('public/assets/js/storefront.js') }}?v=20260916o"></script>
<script src="{{ asset('public/assets/js/home.js') }}?v=20260916d"></script>
<script src="{{ asset('public/assets/js/product.js') }}?v=20260910a"></script>
<script src="{{ asset('public/assets/js/collection.js') }}?v=20260916q"></script>
<script src="{{ asset('public/assets/js/bridal.js') }}?v=20260916q"></script>
<script src="{{ asset('public/assets/js/new-arrivals.js') }}?v=20260916q"></script>
<script src="{{ asset('public/assets/js/offers.js') }}"></script>
<script src="{{ asset('public/assets/js/auth.js') }}"></script>
<script src="{{ asset('public/assets/js/cart.js') }}?v=20260916k"></script>
<script src="{{ asset('public/assets/js/checkout.js') }}?v=20260827d"></script>
<script src="{{ asset('public/assets/js/wishlist.js') }}"></script>
