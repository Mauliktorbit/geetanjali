@props(['item' => []])

@php
    $name = $item['name'] ?? 'Jewellery Piece';
    $removeUrl = route('wishlist.remove', $item['id']);
    $moveUrl = route('wishlist.move', $item['id']);
    $viewUrl = $item['url'] ?? route('products.new-arrivals');
@endphp

<article
    class="wishlist-card"
    data-wishlist-item
    data-product-id="{{ $item['id'] }}"
    data-remove-url="{{ $removeUrl }}"
    data-move-url="{{ $moveUrl }}"
>
    <div class="wishlist-card__media">
        <a href="{{ $viewUrl }}" aria-label="View {{ $name }}">
            <img
                src="{{ asset($item['image']) }}"
                alt="{{ $name }}"
                width="400"
                height="400"
                loading="lazy"
            >
        </a>
        <button
            type="button"
            class="wishlist-card__remove"
            data-wishlist-remove
            aria-label="Remove {{ $name }} from wishlist"
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
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
            @if (! empty($item['discount_label']))
                <span class="is-off">{{ $item['discount_label'] }}</span>
            @endif
        </div>

        <div class="wishlist-card__actions">
            <button type="button" class="wishlist-card__btn wishlist-card__btn--bag" data-wishlist-move>
                Move to Bag
            </button>
            <a href="{{ $viewUrl }}" class="wishlist-card__btn wishlist-card__btn--view">
                View
            </a>
        </div>
    </div>
</article>
