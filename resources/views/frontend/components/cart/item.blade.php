@props(['item' => []])

<article class="cart-item" data-cart-item data-product-id="{{ $item['id'] }}" data-unit-price="{{ $item['price'] }}">
    <div class="cart-item__product">
        <a href="{{ $item['url'] ?? '#' }}" class="cart-item__image">
            <img
                src="{{ storefront_image($item['image'] ?? null) }}"
                alt="{{ $item['name'] }}"
                width="110"
                height="110"
                loading="lazy"
            >
        </a>
        <div class="cart-item__info">
            <h3>
                <a href="{{ $item['url'] ?? '#' }}">{{ $item['name'] }}</a>
            </h3>
            <p>{{ $item['metal'] ?? '' }}</p>
            <p>Weight: {{ $item['weight'] ?? '' }}</p>
        </div>
    </div>

    @php $itemDiscount = price_discount_label($item['price'] ?? 0, $item['compare_at_price'] ?? 0); @endphp
    <div class="cart-item__price">
        <span class="is-current">₹{{ number_format($item['price']) }}</span>
        @if (!empty($item['compare_at_price']))
            <span class="is-old">₹{{ number_format($item['compare_at_price']) }}</span>
        @endif
        @if ($itemDiscount)
            <span class="is-off">{{ $itemDiscount }}</span>
        @endif
    </div>

    <div class="cart-item__qty" role="group" aria-label="Quantity for {{ $item['name'] }}">
        <button type="button" data-qty-minus aria-label="Decrease quantity">−</button>
        <input type="number" min="1" max="10" value="{{ $item['quantity'] }}" data-qty-input aria-label="Quantity">
        <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
    </div>

    <div class="cart-item__total" data-line-total>
        ₹{{ number_format($item['line_total']) }}
    </div>

    <form method="POST" action="{{ route('cart.remove', $item['id']) }}" data-remove-form>
        @csrf
        @method('DELETE')
        <button type="submit" class="cart-item__remove" aria-label="Remove {{ $item['name'] }} from cart">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </form>
</article>
