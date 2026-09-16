<section class="newsletter" aria-labelledby="newsletter-heading">
    <div class="site-container">
        <div class="newsletter__inner">
            <div class="newsletter__copy">
                <h2 id="newsletter-heading" class="font-heading">Stay Close to Geetanjali</h2>
                <p>Subscribe for new arrivals, jewellery stories and collection updates.</p>
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
