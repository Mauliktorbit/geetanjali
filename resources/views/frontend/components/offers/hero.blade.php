@props(['hero' => []])

<section class="offers-hero" aria-labelledby="offers-hero-heading">
    <div class="offers-hero__bg" aria-hidden="true">
        <img
            src="{{ asset($hero['image'] ?? 'public/assets/images/offers/hero.jpg') }}"
            alt="{{ $hero['image_alt'] ?? 'Gold bangles and emerald jewellery on emerald silk' }}"
            width="1600"
            height="700"
            loading="eager"
            fetchpriority="high"
        >
        <div class="offers-hero__overlay"></div>
    </div>

    <div class="offers-hero__inner">
        <div class="container">
            <div class="offers-hero__content">
                <h1 id="offers-hero-heading" class="font-heading">{{ $hero['title'] ?? 'Exciting Offers' }}</h1>
                <p class="offers-hero__subtitle font-heading">{{ $hero['subtitle'] ?? 'For Every Celebration' }}</p>
                <p class="offers-hero__desc">{{ $hero['description'] ?? '' }}</p>
                <div class="offers-hero__divider" aria-hidden="true">
                    <span></span>
                    <i class="bi bi-diamond-fill"></i>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</section>
