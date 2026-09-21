@props(['item' => [], 'readonly' => false])

@php
    $name = $item['name'] ?? 'Jewellery Piece';
    $removeUrl = route('wishlist.remove', $item['id']);
    $moveUrl = route('wishlist.move', $item['id']);
    $viewUrl = $item['url'] ?? route('products.new-arrivals');
    $itemDiscount = price_discount_label($item['price'] ?? 0, $item['compare_at_price'] ?? 0);
@endphp

<article
    class="wishlist-card"
    data-wishlist-item
    data-product-id="{{ $item['id'] }}"
    @if (! $readonly)
        data-remove-url="{{ $removeUrl }}"
        data-move-url="{{ $moveUrl }}"
    @endif
    data-product-name="{{ $name }}"
    data-product-price="{{ $item['price'] ?? 0 }}"
    data-product-image="{{ storefront_image($item['image'] ?? null) }}"
    data-product-url="{{ $viewUrl }}"
    data-product-slug="{{ $item['slug'] ?? '' }}"
    data-product-compare="{{ $item['compare_at_price'] ?? '' }}"
    data-product-discount="{{ $itemDiscount ?? '' }}"
    data-product-metal="{{ $item['metal'] ?? '' }}"
    data-product-weight="{{ $item['weight'] ?? '' }}"
>
    <div class="wishlist-card__media">
        <a href="{{ $viewUrl }}" aria-label="View {{ $name }}">
            <img
                src="{{ storefront_image($item['image'] ?? null) }}"
                alt="{{ $name }}"
                width="400"
                height="400"
                loading="lazy"
            >
        </a>
        @if (! $readonly)
            <button
                type="button"
                class="wishlist-card__remove"
                data-wishlist-remove
                aria-label="Remove {{ $name }} from wishlist"
            >
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    <div class="wishlist-card__body">
        <h3 class="wishlist-card__name">
            <a href="{{ $viewUrl }}">{{ $name }}</a>
        </h3>

        <div class="wishlist-card__pricing">
            <span class="is-current">₹{{ number_format($item['price']) }}</span>
            @if (! empty($item['compare_at_price']))
                <span class="is-old">₹{{ number_format($item['compare_at_price']) }}</span>
            @endif
            @if ($itemDiscount)
                <span class="is-off">{{ $itemDiscount }}</span>
            @endif
        </div>

        <div class="wishlist-card__actions">
            @if ($readonly)
                <button type="button" class="wishlist-card__btn wishlist-card__btn--bag" data-shared-add>
                    Add to Bag
                </button>
            @else
                <button type="button" class="wishlist-card__btn wishlist-card__btn--bag" data-wishlist-move>
                    Move to Bag
                </button>
            @endif
            <a href="{{ $viewUrl }}" class="wishlist-card__btn wishlist-card__btn--view">
                View
            </a>
        </div>
    </div>
</article>
