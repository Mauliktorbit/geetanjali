@props(['product'])

<div class="product-lightbox" data-product-lightbox aria-hidden="true">
    <div class="product-lightbox__inner">
        <button type="button" class="lightbox-close" data-lightbox-close aria-label="Close image viewer">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
        <button type="button" class="lightbox-nav prev" data-lightbox-prev aria-label="Previous image">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
        <img src="{{ storefront_image($product->images[0]->url ?? null) }}" alt="{{ $product->name }}" data-lightbox-image>
        <button type="button" class="lightbox-nav next" data-lightbox-next aria-label="Next image">
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</div>
