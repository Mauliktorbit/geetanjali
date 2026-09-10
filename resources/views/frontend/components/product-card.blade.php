@props([
    'product' => [],
    'showCart' => true,
])

@php
    $name = $product['name'] ?? 'Jewellery Piece';
    $image = $product['image'] ?? '';
    $price = $product['price'] ?? 0;
    $compareAt = $product['compare_at_price'] ?? null;
    $discount = $product['discount_label'] ?? null;
    $badge = $product['badge'] ?? null;
    $rating = $product['rating'] ?? null;
    $reviewCount = $product['review_count'] ?? null;
    $productId = $product['id'] ?? null;
    $url = $product['url'] ?? (! empty($product['slug']) ? route('products.show', $product['slug']) : null);
    $outOfStock = ($product['stock_status'] ?? 'in_stock') === 'out_of_stock'
        || (isset($product['stock']) && (int) $product['stock'] <= 0);
@endphp

<article
    class="product-card{{ $outOfStock ? ' product-card--oos' : '' }}"
    @if ($productId) data-product-id="{{ $productId }}" @endif
    @if ($outOfStock) data-out-of-stock="1" @endif
    data-product-name="{{ $name }}"
    data-product-price="{{ $price }}"
    data-product-image="{{ storefront_image($image) }}"
    data-product-url="{{ $url ?? '' }}"
    data-product-slug="{{ $product['slug'] ?? '' }}"
    data-product-compare="{{ $compareAt ?? '' }}"
    data-product-discount="{{ $discount ?? '' }}"
    data-product-metal="{{ $product['metal'] ?? '' }}"
    data-product-weight="{{ $product['weight'] ?? '' }}"
>
    <div class="product-card__media">
        @if ($badge)
            <span class="product-card__badge">{{ $badge }}</span>
        @endif
        @if ($outOfStock)
            <span class="product-card__oos">
                <span class="product-card__oos-text">Out of stock</span>
            </span>
        @endif
        @if ($url)
            <a href="{{ $url }}" aria-label="View {{ $name }}">
                <img
                    src="{{ storefront_image($image) }}"
                    alt="{{ $name }}"
                    loading="lazy"
                    width="400"
                    height="400"
                >
            </a>
        @else
            <img
                src="{{ storefront_image($image) }}"
                alt="{{ $name }}"
                loading="lazy"
                width="400"
                height="400"
            >
        @endif
        <button type="button" class="product-card__wishlist" aria-label="Add {{ $name }} to wishlist" data-wishlist-toggle>
            <i class="bi bi-heart" aria-hidden="true"></i>
        </button>
    </div>
    <div class="product-card__body">
        <h3 class="product-card__name">
            @if ($url)
                <a href="{{ $url }}" style="color: inherit; text-decoration: none;">{{ $name }}</a>
            @else
                {{ $name }}
            @endif
        </h3>
        <div class="product-card__pricing">
            <div>
                <span class="price-current">₹{{ number_format($price) }}</span>
                @if ($compareAt)
                    <span class="price-original">₹{{ number_format($compareAt) }}</span>
                @endif
                @if ($discount)
                    <div class="price-discount">{{ $discount }}</div>
                @endif
                @if ($rating !== null)
                    <div class="product-card__rating" aria-label="Rated {{ $rating }} out of 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($rating) ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                        @endfor
                        @if ($reviewCount !== null)
                            <span>({{ $reviewCount }})</span>
                        @endif
                    </div>
                @endif
            </div>
            @if ($showCart && ! $outOfStock)
                <button
                    type="button"
                    class="btn-add-cart"
                    aria-label="Add {{ $name }} to cart"
                    data-add-to-cart
                    @if ($productId) data-product-id="{{ $productId }}" @endif
                >
                    <i class="bi bi-bag" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    </div>
</article>
