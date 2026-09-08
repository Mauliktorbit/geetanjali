@props(['hero' => []])

<section class="na-hero" aria-labelledby="na-hero-heading">
    <div class="na-hero__bg" aria-hidden="true">
        <img
            src="{{ asset($hero['image'] ?? 'public/assets/images/collections/new-arrivals/hero.jpg') }}"
            alt="{{ $hero['image_alt'] ?? 'Latest gold and kundan jewellery arrivals' }}"
            width="1600"
            height="600"
            loading="eager"
            fetchpriority="high"
        >
        <div class="na-hero__overlay"></div>
    </div>

    <div class="na-hero__inner">
        <div class="container">
            <div class="na-hero__content">
                <h1 id="na-hero-heading" class="font-heading">{{ $hero['title'] ?? 'New Arrivals' }}</h1>
                @include('frontend.components.gold-divider', ['align' => 'left'])
                <p>{{ $hero['subtitle'] ?? 'Discover our latest additions to the collection' }}</p>
            </div>
        </div>
    </div>
</section>
