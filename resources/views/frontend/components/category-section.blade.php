@props(['categories' => []])

@if (!empty($categories))
<section class="category-strip" aria-label="Shop by category">
    <div class="site-container">
        <div class="category-slider">
            <div class="category-track" data-category-slider>
                @foreach ($categories as $category)
                    <div>
                        <a href="{{ $category['url'] ?? '#' }}" class="category-item">
                            <div class="category-item__image">
                                <img
                                    src="{{ storefront_image($category['image'] ?? null) }}"
                                    alt="{{ $category['name'] }}"
                                    loading="lazy"
                                    width="118"
                                    height="118"
                                >
                            </div>
                            <p class="category-item__label">{{ $category['name'] }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
