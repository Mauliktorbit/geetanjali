@props(['hero' => []])

<section class="kundan-hero" aria-labelledby="kundan-hero-heading">
    <div class="kundan-hero__bg" aria-hidden="true">
        <img
            src="{{ asset($hero['image'] ?? 'public/assets/images/collections/kundan/hero.jpg') }}"
            alt="{{ $hero['image_alt'] ?? 'Premium Kundan jewellery on emerald silk' }}"
            width="1600"
            height="900"
            loading="eager"
            fetchpriority="high"
        >
        <div class="kundan-hero__overlay"></div>
    </div>

    <div class="kundan-hero__inner">
        <div class="container">
            <div class="kundan-hero__content">
                <h1 id="kundan-hero-heading" class="font-heading">{{ $hero['heading'] ?? 'Kundan Collection' }}</h1>
                <p class="kundan-hero__subtitle font-heading">{{ $hero['subtitle'] ?? '' }}</p>
                <p class="kundan-hero__desc">{{ $hero['description'] ?? '' }}</p>

                <ul class="kundan-hero__features" aria-label="Collection highlights">
                    <li>
                        <span class="kundan-hero__icon" aria-hidden="true">
                            <i class="bi bi-hand-index-thumb"></i>
                        </span>
                        <span>Handcrafted<br>Excellence</span>
                    </li>
                    <li>
                        <span class="kundan-hero__icon" aria-hidden="true">
                            <i class="bi bi-gem"></i>
                        </span>
                        <span>Authentic<br>Kundan Stones</span>
                    </li>
                    <li>
                        <span class="kundan-hero__icon" aria-hidden="true">
                            <i class="bi bi-award"></i>
                        </span>
                        <span>Heritage<br>Craftsmanship</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
