@props([
    'heading' => 'Stay Updated with Our Latest Collections',
    'description' => 'Be the first to know about new arrivals and jewellery stories.',
    'image' => 'public/assets/images/about/newsletter-gift.jpg',
])

<section class="about-newsletter" aria-labelledby="about-newsletter-heading">
    <div class="site-container">
        <div class="about-newsletter__grid">
            <div class="about-newsletter__media reveal">
                <img
                    src="{{ asset($image) }}"
                    alt="Gift packaging from Geetanjali Jewellers"
                    loading="lazy"
                    width="120"
                    height="120"
                >
            </div>

            <div class="about-newsletter__copy reveal">
                <h2 id="about-newsletter-heading">{{ $heading }}</h2>
                <p>{{ $description }}</p>
            </div>

            <form class="about-newsletter__form reveal" action="{{ route('newsletter.subscribe') }}" method="post" aria-label="Newsletter subscription">
                @csrf
                <label class="visually-hidden" for="about-newsletter-email">Email address</label>
                <input
                    id="about-newsletter-email"
                    type="email"
                    name="email"
                    placeholder="Enter your email address"
                    required
                    autocomplete="email"
                >
                <button type="submit" class="btn-subscribe">Subscribe</button>
            </form>
        </div>
    </div>
</section>
