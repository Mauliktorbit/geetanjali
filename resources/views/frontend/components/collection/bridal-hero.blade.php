@props(['hero' => []])

<section class="bridal-hero" aria-labelledby="bridal-hero-heading">
    <div class="bridal-hero__bg" aria-hidden="true">
        <img
            src="{{ asset($hero['image'] ?? 'public/assets/images/collections/bridal/hero.jpg') }}"
            alt="{{ $hero['image_alt'] ?? 'Indian bride in traditional bridal jewellery' }}"
            width="1600"
            height="900"
            loading="eager"
            fetchpriority="high"
        >
        <div class="bridal-hero__overlay"></div>
    </div>

    <div class="bridal-hero__inner">
        <div class="container">
            <div class="bridal-hero__content">
                @if (!empty($hero['label']))
                    <p class="bridal-hero__label">{{ $hero['label'] }}</p>
                @endif

                <h1 id="bridal-hero-heading" class="font-heading">
                    <span>{{ $hero['heading_line_1'] ?? 'Timeless Beauty for' }}</span>
                    <span class="bridal-hero__accent">{{ $hero['heading_line_2'] ?? 'Your Special Day' }}</span>
                </h1>

                <div class="bridal-hero__divider" aria-hidden="true">
                    <span></span>
                    <i class="bi bi-diamond-fill"></i>
                    <span></span>
                </div>

                <p class="bridal-hero__desc">{{ $hero['description'] ?? '' }}</p>

                <a href="{{ $hero['cta_url'] ?? '#bridal-products' }}" class="bridal-btn bridal-btn--hero">
                    {{ $hero['cta_label'] ?? 'Explore Collection' }}
                </a>
            </div>
        </div>
    </div>
</section>
