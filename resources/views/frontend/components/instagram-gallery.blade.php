@props(['images' => []])

<section class="section" aria-labelledby="gallery-heading">
    <div class="site-container">
        <h2 id="gallery-heading" class="section-title reveal">Follow Our Journey @geetanjalijewellers</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>
    </div>

    <div class="gallery-grid">
        @foreach ($images as $image)
            <a href="{{ $image['url'] ?? config('brand.social.instagram') }}" class="gallery-item" aria-label="View Instagram jewellery post" target="_blank" rel="noopener noreferrer">
                <img
                    src="{{ asset($image['src']) }}"
                    alt="{{ $image['alt'] ?? 'Geetanjali jewellery' }}"
                    loading="lazy"
                >
                <span class="gallery-item__overlay" aria-hidden="true">
                    <i class="bi bi-instagram"></i>
                </span>
            </a>
        @endforeach
    </div>
</section>
