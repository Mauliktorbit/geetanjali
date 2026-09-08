@props(['occasions' => []])

<section class="section" id="occasions" aria-labelledby="occasions-heading">
    <div class="site-container">
        <h2 id="occasions-heading" class="section-title reveal">Shop by Occasion</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>

        <div class="occasion-grid">
            @foreach ($occasions as $occasion)
                <a href="{{ $occasion['url'] ?? '#' }}" class="occasion-card reveal">
                    <img
                        src="{{ asset($occasion['image']) }}"
                        alt="{{ $occasion['name'] }} jewellery"
                        loading="lazy"
                    >
                    <div class="occasion-card__overlay">
                        <h3 class="occasion-card__title font-heading">{{ $occasion['name'] }}</h3>
                        <span class="btn-shop">Shop Now</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
