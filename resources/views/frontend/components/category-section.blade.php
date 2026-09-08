@props(['categories' => []])

<section class="category-strip" aria-label="Shop by category">
    <div class="site-container">
        <div class="category-track">
            @foreach ($categories as $category)
                <a href="{{ $category['url'] ?? '#' }}" class="category-item reveal">
                    <div class="category-item__image">
                        <img
                            src="{{ asset($category['image']) }}"
                            alt="{{ $category['name'] }}"
                            loading="lazy"
                            width="118"
                            height="118"
                        >
                    </div>
                    <p class="category-item__label">{{ $category['name'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
