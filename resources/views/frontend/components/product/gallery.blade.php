@props(['product'])

@php
    $images = $product->images ?? [];
@endphp

<div class="product-gallery" data-product-gallery>
    <div class="product-gallery__main">
        @if (!empty($product->badge))
            <span class="product-badge">{{ $product->badge }}</span>
        @endif

        <button
            type="button"
            class="product-wish"
            data-wishlist-toggle
            aria-label="Add {{ $product->name }} to wishlist"
            aria-pressed="false"
        >
            <i class="bi bi-heart" aria-hidden="true"></i>
        </button>

        <img
            src="{{ asset($images[0]->url ?? 'public/assets/images/products/gallery/main.jpg') }}"
            alt="{{ $images[0]->alt ?? $product->name }}"
            data-main-image
            loading="eager"
            width="720"
            height="720"
        >

        <button type="button" class="product-zoom-btn" data-open-zoom>
            <i class="bi bi-zoom-in" aria-hidden="true"></i>
            Click to Zoom
        </button>
    </div>

    <div class="product-thumbs-wrap">
        <button type="button" class="thumb-nav" data-thumb-prev aria-label="Previous images">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>

        <div class="product-thumbs" data-thumbs>
            @foreach ($images as $index => $image)
                <button
                    type="button"
                    class="product-thumb {{ $index === 0 ? 'is-active' : '' }}"
                    data-thumb
                    data-full="{{ asset($image->url) }}"
                    data-alt="{{ $image->alt }}"
                    aria-label="View image {{ $index + 1 }}"
                >
                    <img
                        src="{{ asset($image->url) }}"
                        alt="{{ $image->alt }}"
                        loading="lazy"
                        width="96"
                        height="96"
                    >
                </button>
            @endforeach
        </div>

        <button type="button" class="thumb-nav" data-thumb-next aria-label="Next images">
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</div>
