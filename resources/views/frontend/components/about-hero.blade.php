@props([
    'heading_line_1' => 'A Legacy of Trust,',
    'heading_line_2' => 'Crafted in Gold',
    'description' => 'For over three decades, Geetanjali Jewellers has been a name synonymous with trust, elegance and unmatched craftsmanship.',
    'image' => 'public/assets/images/about/about-hero.jpg',
    'image_alt' => 'Geetanjali Jewellers premium Kundan jewellery on silk',
])

<section
    class="about-hero"
    aria-labelledby="about-hero-heading"
    style="--about-hero-image: url('{{ asset($image) }}');"
>
    <div
        class="about-hero__visual"
        role="img"
        aria-label="{{ $image_alt }}"
    ></div>

    <div class="container about-hero__inner">
        <div class="about-hero__content reveal">
            <span class="section-label">About Us</span>
            <h1 id="about-hero-heading" class="about-heading">
                <span class="break">{{ $heading_line_1 }}</span>
                <span class="break">{{ $heading_line_2 }}</span>
            </h1>
            @include('frontend.components.gold-divider')
            <p class="about-body">{{ $description }}</p>
        </div>
    </div>
</section>
