@props([
    'services' => [],
    'variant' => 'top',
])

<section
    class="bridal-services bridal-services--{{ $variant }}"
    aria-label="{{ $variant === 'trust' ? 'Quality assurances' : 'Service benefits' }}"
>
    <div class="site-container">
        <div class="bridal-services__grid">
            @foreach ($services as $service)
                <div class="bridal-services__item">
                    <i class="bi {{ $service['icon'] }}" aria-hidden="true"></i>
                    <div>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['subtitle'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
