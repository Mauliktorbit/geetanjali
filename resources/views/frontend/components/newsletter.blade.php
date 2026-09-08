<section class="newsletter" aria-labelledby="newsletter-heading">
    <div class="site-container">
        <div class="newsletter__inner">
            <div class="newsletter__copy">
                <h2 id="newsletter-heading" class="font-heading">Get 10% Off on Your First Order!</h2>
                <p>Subscribe for jewellery tips, latest arrivals and exclusive offers.</p>
            </div>

            <form class="newsletter__form" action="{{ route('newsletter.subscribe') }}" method="post" aria-label="Newsletter subscription">
                @csrf
                <label class="visually-hidden" for="newsletter-email">Email address</label>
                <input
                    id="newsletter-email"
                    type="email"
                    name="email"
                    placeholder="Enter your email address"
                    required
                    autocomplete="email"
                >
                <button type="submit" class="btn-gold">Subscribe</button>
            </form>
        </div>
    </div>
</section>
