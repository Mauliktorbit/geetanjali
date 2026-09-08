@props(['slides' => []])

<section class="hero" aria-label="Featured collections">
    <div
        id="heroCarousel"
        class="carousel slide carousel-fade hero-carousel"
        data-bs-ride="carousel"
        data-bs-interval="5000"
        data-bs-pause="hover"
        data-bs-wrap="true"
    >
        <div class="carousel-indicators">
            @foreach ($slides as $index => $slide)
                <button
                    type="button"
                    data-bs-target="#heroCarousel"
                    data-bs-slide-to="{{ $index }}"
                    class="{{ $index === 0 ? 'active' : '' }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="Slide {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>

        <div class="carousel-inner">
            @foreach ($slides as $index => $slide)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" data-bs-interval="5000">
                    <div class="hero-slide">
                        <div class="hero-slide__media">
                            <img
                                src="{{ asset($slide['image']) }}"
                                alt="{{ $slide['image_alt'] }}"
                                @if ($index > 0) loading="lazy" @endif
                            >
                        </div>
                        <div class="hero-slide__overlay" aria-hidden="true"></div>

                        <div class="hero-slide__content">
                            <div class="container">
                                <div class="hero-slide__copy">
                                    @if (!empty($slide['kicker']))
                                        <span class="hero-kicker">{{ $slide['kicker'] }}</span>
                                    @endif
                                    <h1 class="hero-title">
                                        <span class="line-white">{{ $slide['title_line_1'] }}</span>
                                        <span class="line-gold">{{ $slide['title_line_2'] }}</span>
                                    </h1>
                                    <p class="hero-text">{{ $slide['description'] }}</p>
                                    <div class="hero-divider" aria-hidden="true"></div>
                                    <a href="{{ $slide['cta_url'] ?? '#bestsellers' }}" class="btn-gold">
                                        {{ $slide['cta_label'] ?? 'Shop Now' }}
                                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
