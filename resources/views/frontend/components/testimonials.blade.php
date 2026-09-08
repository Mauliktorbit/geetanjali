@props(['testimonials' => []])

<section class="section testimonials" aria-labelledby="testimonials-heading">
    <div class="site-container">
        <h2 id="testimonials-heading" class="section-title reveal">What Our Customers Say</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>

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
    </div>
</section>
