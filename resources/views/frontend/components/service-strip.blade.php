@props(['services' => []])

<section class="service-strip" aria-label="Our promises">
    <div class="site-container">
        <div class="service-grid">
            @foreach ($services as $service)
                <div class="service-item reveal">
                    <i class="bi {{ $service['icon'] }}" aria-hidden="true"></i>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['subtitle'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
