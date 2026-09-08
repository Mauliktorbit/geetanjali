@props(['values' => []])

@php
    $values = $values ?: [
        [
            'icon' => 'bi-gem',
            'title' => 'Trust & Transparency',
            'description' => 'Honest pricing, certified products and transparent policies.',
        ],
        [
            'icon' => 'bi-award',
            'title' => 'Quality Craftsmanship',
            'description' => 'Every piece is crafted with precision and attention to detail.',
        ],
        [
            'icon' => 'bi-people',
            'title' => 'Customer First',
            'description' => 'Your satisfaction and trust are at the heart of everything we do.',
        ],
        [
            'icon' => 'bi-pen',
            'title' => 'Timeless Designs',
            'description' => 'Blending tradition with contemporary designs for every generation.',
        ],
        [
            'icon' => 'bi-heart',
            'title' => 'Relationships That Last',
            'description' => 'Building lifelong relationships through trust, care and service.',
        ],
    ];
@endphp

<section class="about-values" aria-labelledby="values-heading">
    <div class="site-container">
        <div class="about-values__panel reveal">
            <div class="text-center">
                <span class="section-label">Our Values</span>
                <h2 id="values-heading" class="visually-hidden">Our Values</h2>
                @include('frontend.components.gold-divider', ['align' => 'center'])
            </div>

            <div class="about-values__grid">
                @foreach ($values as $value)
                    <article class="value-card">
                        <div class="value-card__icon" aria-hidden="true">
                            <i class="bi {{ $value['icon'] }}"></i>
                        </div>
                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
