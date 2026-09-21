@props(['testimonials' => []])

<section class="section testimonials" aria-labelledby="testimonials-heading">
    <div class="site-container">
        <h2 id="testimonials-heading" class="section-title reveal">{{ count($testimonials) > 0 ? 'What Our Customers Say' : 'The Geetanjali Promise' }}</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>

        @if (count($testimonials) > 0)
        <div class="testimonial-grid">
            @foreach ($testimonials as $index => $item)
                <article class="testimonial-card reveal" data-testimonial-card>
                    <span class="testimonial-card__quote" aria-hidden="true">
                        <i class="bi bi-quote"></i>
                    </span>
                    <div class="testimonial-stars" aria-label="{{ $item['rating'] ?? 5 }} out of 5 stars">
                        @for ($i = 0; $i < ($item['rating'] ?? 5); $i++)
                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                        @endfor
                    </div>
                    <p>“{{ $item['review'] }}”</p>
                    <strong>{{ $item['name'] }}</strong>
                    <span>{{ $item['city'] }}</span>
                </article>
            @endforeach
        </div>

        <div class="testimonial-dots d-md-none" role="tablist" aria-label="Testimonial slides">
            @foreach ($testimonials as $index => $item)
                <button
                    type="button"
                    data-testimonial-dot
                    class="{{ $index === 0 ? 'is-active' : '' }}"
                    aria-label="Show review {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>
        @else
        <div class="testimonial-grid">
            <article class="testimonial-card reveal">
                <strong>Skin-friendly Finish</strong>
                <p>Anti-tarnish plating so pieces stay comfortable and bright with daily wear.</p>
            </article>
            <article class="testimonial-card reveal">
                <strong>Made in Our Workshop</strong>
                <p>Kundan jewellery crafted by our artisans in Ahmedabad, from design to the final setting.</p>
            </article>
            <article class="testimonial-card reveal">
                <strong>Visit the Showroom</strong>
                <p>Try on bridal and daily-wear pieces at PNTC Tower, Vejalpur. Mon–Sat, 10 AM to 7 PM.</p>
            </article>
        </div>
        @endif
    </div>
</section>
