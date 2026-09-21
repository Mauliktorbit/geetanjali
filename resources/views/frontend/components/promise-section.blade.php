@props([
    'heading_line_1' => 'Crafted with Passion,',
    'heading_line_2' => 'Delivered with Pride',
    'description' => 'Each creation goes through a meticulous process to ensure it meets our high standards of finish, comfort and design.',
    'features' => [],
    'image' => 'public/assets/images/about/craftsmanship.jpg',
    'image_alt' => 'Artisan crafting fine Geetanjali jewellery by hand',
])

@php
    $features = $features ?: [
        [
            'icon' => 'bi-heart',
            'title' => 'Skin-friendly Jewellery',
            'description' => 'Lightweight artificial jewellery with a premium anti-tarnish finish, made for everyday wear.',
        ],
        [
            'icon' => 'bi-truck',
            'title' => 'Secure & Insured Delivery',
            'description' => 'Your precious jewellery is delivered safely, securely and on time.',
        ],
        [
            'icon' => 'bi-headset',
            'title' => 'After-Sales Support',
            'description' => 'We are always here to assist you, even after your purchase.',
        ],
    ];
@endphp

<section class="about-promise" id="promise" aria-labelledby="promise-heading">
    <div class="site-container">
        <div class="about-promise__grid">
            <div class="about-promise__content reveal">
                <span class="section-label">The Geetanjali Promise</span>
                <h2 id="promise-heading" class="about-heading">
                    <span class="break">{{ $heading_line_1 }}</span>
                    <span class="break">{{ $heading_line_2 }}</span>
                </h2>
                @include('frontend.components.gold-divider')
                <p class="about-body">{{ $description }}</p>

                <ul class="promise-list">
                    @foreach ($features as $feature)
                        <li class="promise-item">
                            <span class="promise-item__icon" aria-hidden="true">
                                <i class="bi {{ $feature['icon'] }}"></i>
                            </span>
                            <div>
                                <h3>{{ $feature['title'] }}</h3>
                                <p>{{ $feature['description'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="about-promise__media reveal">
                <img
                    src="{{ asset($image) }}"
                    alt="{{ $image_alt }}"
                    loading="lazy"
                    width="720"
                    height="480"
                >
            </div>
        </div>
    </div>
</section>
