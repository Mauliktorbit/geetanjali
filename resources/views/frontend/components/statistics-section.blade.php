@props(['stats' => []])

@php
    $stats = $stats ?: [
        ['icon' => 'bi-shield-check', 'value' => '30+', 'label' => 'Years of Trust'],
        ['icon' => 'bi-people', 'value' => '1,00,000+', 'label' => 'Happy Customers'],
        ['icon' => 'bi-gem', 'value' => '10,000+', 'label' => 'Unique Designs'],
        ['icon' => 'bi-geo-alt', 'value' => '25+', 'label' => 'Stores Across India'],
    ];
@endphp

<section class="about-stats" aria-label="Geetanjali trust statistics">
    <div class="site-container">
        <div class="about-stats__grid">
            @foreach ($stats as $stat)
                <div class="stat-item reveal">
                    <i class="bi {{ $stat['icon'] }} stat-item__icon" aria-hidden="true"></i>
                    <span class="stat-item__number">{{ $stat['value'] }}</span>
                    <span class="stat-item__label">{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
