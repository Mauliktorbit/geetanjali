@props(['banner' => []])

<section class="kundan-banner" id="kundan" aria-labelledby="kundan-heading">
    <div class="kundan-banner__media">
        <img
            src="{{ asset($banner['image'] ?? 'public/assets/images/banners/kundan-collection.jpg') }}"
            alt="{{ $banner['image_alt'] ?? 'Exclusive Kundan jewellery collection' }}"
            loading="lazy"
        >
    </div>
    <div class="kundan-banner__overlay" aria-hidden="true"></div>
    <div class="site-container">
        <div class="kundan-banner__content reveal">
            <div>
                <h2 id="kundan-heading">
                    <span class="d-block">{{ $banner['title_line_1'] ?? 'EXCLUSIVE KUNDAN' }}</span>
                    <span class="d-block">{{ $banner['title_line_2'] ?? 'COLLECTION' }}</span>
                </h2>
                <p>{{ $banner['description'] ?? 'Handcrafted Heritage. Made for Modern You.' }}</p>
                <a href="{{ $banner['cta_url'] ?? '#' }}" class="btn-gold">
                    {{ $banner['cta_label'] ?? 'Explore Collection' }}
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
