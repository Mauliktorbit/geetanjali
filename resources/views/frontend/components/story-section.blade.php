@props([
    'heading_line_1' => 'Where Tradition',
    'heading_line_2' => 'Meets Timeless Beauty',
    'paragraphs' => [],
    'image' => 'public/assets/images/about/showroom.jpg',
    'image_alt' => 'Geetanjali Jewellers premium jewellery showroom',
    'cta_label' => 'Know More About Us',
    'cta_url' => route('about') . '#promise',
])

@php
    $paragraphs = $paragraphs ?: [
        'Founded with a passion for perfection and a commitment to excellence, Geetanjali Jewellers began its journey with a simple belief — every piece of jewellery tells a story.',
        'From traditional craftsmanship to modern designs, we create jewellery that celebrates every milestone and emotion in your life.',
    ];
@endphp

<section class="about-story" id="our-story" aria-labelledby="story-heading">
    <div class="site-container">
        <div class="about-story__grid">
            <div class="about-story__media reveal">
                <img
                    src="{{ asset($image) }}"
                    alt="{{ $image_alt }}"
                    loading="lazy"
                    width="720"
                    height="480"
                >
            </div>

            <div class="about-story__content reveal">
                <span class="section-label">Our Story</span>
                <h2 id="story-heading" class="about-heading">
                    <span class="break">{{ $heading_line_1 }}</span>
                    <span class="break">{{ $heading_line_2 }}</span>
                </h2>
                @include('frontend.components.gold-divider')

                @foreach ($paragraphs as $paragraph)
                    <p class="about-body">{{ $paragraph }}</p>
                @endforeach

                <a href="{{ $cta_url }}" class="btn-about">
                    {{ $cta_label }}
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
