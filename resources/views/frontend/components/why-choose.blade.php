@props(['features' => []])

<section class="section" id="why" aria-labelledby="why-heading">
    <div class="site-container">
        <h2 id="why-heading" class="section-title reveal">Why Choose Geetanjali?</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>

        <div class="why-grid">
            @foreach ($features as $feature)
                <div class="why-item reveal">
                    <div class="why-item__icon" aria-hidden="true">
                        <i class="bi {{ $feature['icon'] }}"></i>
                    </div>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['subtitle'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
