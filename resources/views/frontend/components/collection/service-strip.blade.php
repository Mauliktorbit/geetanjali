@props(['services' => []])

<section class="kundan-services" aria-label="Jewellery service benefits">
    <div class="site-container">
        <div class="kundan-services__grid">
            @foreach ($services as $service)
                <div class="kundan-services__item">
                    <i class="bi {{ $service['icon'] }}" aria-hidden="true"></i>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['subtitle'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
