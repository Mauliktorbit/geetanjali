@props(['products' => []])

<section class="related-section" aria-labelledby="related-heading">
    <div class="related-head">
        <h2 id="related-heading">You May Also Like</h2>
        <a href="{{ route('products.new-arrivals') }}">View All →</a>
    </div>

    <div class="related-grid">
        @foreach ($products as $item)
            <a class="related-card" href="{{ route('products.show', $item->slug) }}">
                <div class="related-card__media">
                    <img
                        src="{{ asset($item->image) }}"
                        alt="{{ $item->name }}"
                        loading="lazy"
                        width="280"
                        height="280"
                    >
                </div>
                <div class="related-card__body">
                    <h3>{{ $item->name }}</h3>
                    <div class="price">₹{{ number_format($item->price) }}</div>
                    <div class="meta">
                        <span class="stars" aria-hidden="true">★★★★★</span>
                        ({{ $item->review_count }})
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>
