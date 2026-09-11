<footer class="site-footer">
    <div class="footer-main">
        <div class="site-container">
            <div class="footer-grid">
                <div class="footer-brand footer-col">
                    <img
                        src="{{ asset('public/assets/images/logo/geetanjali-logo-footer.png') }}"
                        alt="{{ config('brand.name') }}"
                        width="220"
                        height="60"
                        class="footer-brand__logo"
                    >
                    <p>
                        Crafting timeless jewellery with passion and precision, every piece tells a story of elegance and tradition.
                    </p>
                    <div class="footer-social">
                        <a href="{{ config('brand.social.facebook') }}" aria-label="Facebook">
                            <i class="bi bi-facebook" aria-hidden="true"></i>
                        </a>
                        <a href="{{ config('brand.social.instagram') }}" aria-label="Instagram">
                            <i class="bi bi-instagram" aria-hidden="true"></i>
                        </a>
                        <a href="{{ config('brand.social.youtube') }}" aria-label="YouTube">
                            <i class="bi bi-youtube" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                {{-- On mobile/tablet: Shop + Customer Care side by side --}}
                <div class="footer-pair">
                    <div class="footer-col">
                        <h3>Shop</h3>
                        <ul>
                            @foreach ($storefrontCollections ?? \App\Services\StorefrontCatalogService::navCollections() as $shopCollection)
                                <li>
                                    <a href="{{ \App\Services\StorefrontCatalogService::storefrontUrl($shopCollection) }}">{{ $shopCollection->name }}</a>
                                </li>
                            @endforeach
                            <li><a href="{{ route('offers.index') }}">Offers</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h3>Customer Care</h3>
                        <ul>
                            <li><a href="{{ route('contact') }}">Contact Us</a></li>
                            <li><a href="{{ route('pages.faq') }}">FAQ's</a></li>
                            <li><a href="{{ route('pages.shipping') }}">Shipping Policy</a></li>
                            <li><a href="{{ route('pages.returns') }}">Returns &amp; Refunds</a></li>
                            <li><a href="{{ route('pages.track-order') }}">Track Order</a></li>
                            <li><a href="{{ route('pages.terms') }}">Terms &amp; Conditions</a></li>
                        </ul>
                    </div>
                </div>

                {{-- On mobile/tablet: My Account + Contact Us side by side --}}
                <div class="footer-pair">
                    <div class="footer-col">
                        <h3>My Account</h3>
                        <ul>
                            <li><a href="{{ auth()->check() ? route('account.index') : route('login') }}">My Account</a></li>
                            <li><a href="{{ auth()->check() ? route('account.orders') : route('login') }}">My Orders</a></li>
                            <li><a href="{{ route('wishlist.index') }}">Wishlist</a></li>
                            <li><a href="{{ route('pages.compare') }}">Compare</a></li>
                            <li><a href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                            @auth
                                <li>
                                    <form action="{{ route('logout') }}" method="post" class="d-inline" data-confirm="Log out? You will need to sign in again to view your account.">
                                        @csrf
                                        <button type="submit" class="footer-logout-link" data-confirm="Log out? You will need to sign in again to view your account.">Logout</button>
                                    </form>
                                </li>
                            @else
                                <li><a href="{{ route('login') }}">Login</a></li>
                            @endauth
                        </ul>
                    </div>

                    <div class="footer-col footer-contact">
                        <h3>Contact Us</h3>
                        <ul>
                            <li>
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <span>{{ config('brand.contact.address') }}</span>
                            </li>
                            <li>
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <a href="tel:{{ preg_replace('/\s+/', '', config('brand.contact.phone')) }}">
                                    {{ config('brand.contact.phone') }}
                                </a>
                            </li>
                            <li>
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <a href="mailto:{{ config('brand.contact.email') }}">
                                    {{ config('brand.contact.email') }}
                                </a>
                            </li>
                            <li>
                                <i class="bi bi-clock" aria-hidden="true"></i>
                                <span>{{ config('brand.contact.hours') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="site-container footer-bottom__inner">
            <p>&copy; {{ date('Y') }} {{ config('brand.name') }}. All Rights Reserved.</p>
            <div class="payment-icons" aria-label="Accepted payment methods">
                <span>VISA</span>
                <span>Mastercard</span>
                <span>UPI</span>
                <span>Paytm</span>
            </div>
        </div>
    </div>
</footer>
