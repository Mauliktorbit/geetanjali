@props(['stats' => []])

@php
    $stats = $stats ?: [
        ['icon' => 'bi-geo-alt', 'value' => 'Ahmedabad', 'label' => 'Showroom at PNTC Tower, Vejalpur'],
        ['icon' => 'bi-hammer', 'value' => 'Workshop', 'label' => 'Kundan jewellery crafted in-house'],
        ['icon' => 'bi-patch-check', 'value' => 'Hallmarked', 'label' => 'BIS-certified gold in every piece'],
        ['icon' => 'bi-clock', 'value' => 'Mon–Sat', 'label' => '10:00 AM – 7:00 PM'],
    ];
@endphp

<section class="about-stats" aria-label="Visit Geetanjali Jewellers">
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
