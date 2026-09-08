@props(['services' => []])

<section class="na-services" aria-label="Service benefits">
    <div class="na-container">
        <div class="na-services__grid">
            @foreach ($services as $service)
                <div class="na-services__item">
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
