@props([
    'product' => [],
    'showCart' => true,
])

@php
    $name = $product['name'] ?? 'Jewellery Piece';
    $image = $product['image'] ?? '';
    $price = $product['price'] ?? 0;
    $compareAt = $product['compare_at_price'] ?? null;
    $discount = price_discount_label($price, $compareAt);
    $badge = $product['badge'] ?? null;
    $productId = $product['id'] ?? null;
    $url = $product['url'] ?? (! empty($product['slug']) ? route('products.show', $product['slug']) : null);
    $similarUrl = $product['similar_url'] ?? $url;
    $outOfStock = ($product['stock_status'] ?? 'in_stock') === 'out_of_stock'
        || (isset($product['stock']) && (int) $product['stock'] <= 0);
    $inWishlist = $productId && in_array((int) $productId, $wishlistProductIds ?? [], true);
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
    data-product-similar="{{ $similarUrl ?? '' }}"
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
        <button
            type="button"
            class="product-card__wishlist{{ $inWishlist ? ' is-active' : '' }}"
            aria-label="{{ $inWishlist ? 'Remove '.$name.' from wishlist' : 'Add '.$name.' to wishlist' }}"
            aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
            data-wishlist-toggle
            @if ($productId) data-product-id="{{ $productId }}" @endif
        >
            <i class="bi {{ $inWishlist ? 'bi-heart-fill' : 'bi-heart' }}" aria-hidden="true"></i>
        </button>
    </div>
    <div class="product-card__actions">
        <button
            type="button"
            class="product-card__action product-card__action--view"
            data-quick-view
            data-no-loading
        >
            <i class="bi bi-eye" aria-hidden="true"></i>
            <span>Quick View</span>
        </button>
        @if (! $outOfStock)
            <button
                type="button"
                class="product-card__action product-card__action--cart"
                aria-label="Add {{ $name }} to cart"
                data-add-to-cart
                data-no-loading
                @if ($productId) data-product-id="{{ $productId }}" @endif
            >
                <i class="bi bi-bag" aria-hidden="true"></i>
                <span>Add to Cart</span>
            </button>
        @else
            <button
                type="button"
                class="product-card__action product-card__action--notify"
                aria-label="Notify me when {{ $name }} is back in stock"
                data-stock-notify
                data-no-loading
                @if ($productId) data-product-id="{{ $productId }}" @endif
            >
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span>Notify me</span>
            </button>
            @if ($similarUrl)
                <a
                    href="{{ $similarUrl }}"
                    class="product-card__action product-card__action--similar"
                >
                    <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                    <span>View similar</span>
                </a>
            @endif
        @endif
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
            @elseif ($showCart && $outOfStock)
                <button
                    type="button"
                    class="btn-add-cart btn-add-cart--notify"
                    aria-label="Notify me when {{ $name }} is back in stock"
                    data-stock-notify
                    data-no-loading
                    @if ($productId) data-product-id="{{ $productId }}" @endif
                >
                    <i class="bi bi-bell" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    </div>
</article>
