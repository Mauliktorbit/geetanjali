@props(['products' => []])

<section class="related-section" aria-labelledby="related-heading">
    <div class="related-head">
        <h2 id="related-heading">You May Also Like</h2>
        <a href="{{ route('products.new-arrivals') }}">View All →</a>
    </div>

    <div class="related-grid">
        @foreach ($products as $item)
            @php $card = is_array($item) ? $item : (array) $item; @endphp
            <a class="related-card" href="{{ $card['url'] ?? route('products.show', $card['slug']) }}">
                <div class="related-card__media">
                    <img
                        src="{{ storefront_image($card['image'] ?? null) }}"
                        alt="{{ $card['name'] }}"
                        loading="lazy"
                        width="280"
                        height="280"
                    >
                </div>
                <div class="related-card__body">
                    <h3>{{ $card['name'] }}</h3>
                    <div class="price">₹{{ number_format($card['price'] ?? 0) }}</div>
                    <div class="meta">
                        <span class="stars" aria-hidden="true">★★★★★</span>
                        ({{ $card['review_count'] ?? 0 }})
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>
