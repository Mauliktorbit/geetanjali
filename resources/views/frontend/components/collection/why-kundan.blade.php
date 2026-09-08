@props(['features' => []])

<section class="kundan-why" aria-labelledby="kundan-why-heading">
    <div class="site-container">
        <div class="kundan-why__inner">
            <span class="kundan-why__floral kundan-why__floral--left" aria-hidden="true"></span>
            <span class="kundan-why__floral kundan-why__floral--right" aria-hidden="true"></span>

            <h2 id="kundan-why-heading" class="font-heading">Why Choose Kundan Jewellery?</h2>
            @include('frontend.components.gold-divider', ['align' => 'center'])

            <div class="kundan-why__grid">
                @foreach ($features as $feature)
                    <article class="kundan-why__item">
                        <div class="kundan-why__icon" aria-hidden="true">
                            <i class="bi {{ $feature['icon'] }}"></i>
                        </div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['subtitle'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
