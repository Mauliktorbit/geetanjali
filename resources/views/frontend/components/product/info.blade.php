@props(['product'])

<div class="product-info">
    <h1 class="product-title">{{ $product->name }}</h1>
    @if (!empty($product->category))
        <p class="product-category">{{ $product->category }}</p>
    @endif

    <div class="product-rating-row">
        <span class="product-stars" aria-label="{{ $product->rating }} out of 5 stars">
            @for ($i = 1; $i <= 5; $i++)
                <i class="bi {{ $i <= floor($product->rating) ? 'bi-star-fill' : ($i - $product->rating < 1 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
            @endfor
        </span>
        <strong>{{ number_format((float) $product->rating, 1) }}</strong>
        <a href="#product-tabs" data-open-reviews>{{ review_count_label($product->review_count, true) }}</a>
        @if ((int) $product->sold_count > 0)
            <span class="sep" aria-hidden="true">|</span>
            <span>Sold {{ $product->sold_count }}</span>
        @endif
    </div>

    @php
        $displaySale = (float) ($product->sale_price ?: $product->price);
        $displayMrp = (float) $product->price;
        $hasSale = $product->sale_price && $displaySale > 0 && $displaySale < $displayMrp;
        $displayDiscount = $hasSale
            ? ($product->discount_label ?: price_discount_label($displaySale, $displayMrp))
            : null;
        if ($hasSale && ! $displayDiscount) {
            $displayDiscount = 'Save ₹'.number_format((int) round($displayMrp - $displaySale));
        }
    @endphp
    <div class="product-price-block">
        <p class="product-price">
            <span class="product-price__current">₹{{ number_format($displaySale) }}</span>
            @if ($hasSale)
                <span class="product-price__original">₹{{ number_format($displayMrp) }}</span>
            @endif
        </p>
        @if ($displayDiscount)
            <p class="product-price__save">{{ $displayDiscount }}</p>
        @endif
        @if (! empty($product->tax_note))
            <p class="product-price-note">{{ $product->tax_note }}</p>
        @endif
    </div>

    <p class="product-short">{{ $product->short_description }}</p>

    <div class="product-benefits" aria-label="Product benefits">
        @foreach ($product->benefits ?? [] as $benefit)
            <div class="product-benefit">
                <i class="bi {{ $benefit['icon'] }}" aria-hidden="true"></i>
                <span>{{ $benefit['label'] }}</span>
            </div>
        @endforeach
    </div>

    <p class="attr-label-top">Material</p>
    <div class="metal-pill">{{ $product->metal ?: 'Fashion jewellery' }}</div>

    <ul class="product-attrs">
        @if (filled($product->stone))
            <li><span class="label">Stone:</span><span class="value">{{ $product->stone }}</span></li>
        @endif
        @if (filled($product->style))
            <li><span class="label">Style:</span><span class="value">{{ $product->style }}</span></li>
        @endif
        @if (filled($product->weight))
            <li><span class="label">Weight:</span><span class="value">{{ $product->weight }}</span></li>
        @endif
    </ul>

    <div class="stock-status {{ ($product->stock_status ?? '') === 'out_of_stock' ? 'is-out' : '' }}">
        <span class="stock-dot" aria-hidden="true"></span>
        <span>
            @if (($product->stock_status ?? 'in_stock') === 'in_stock' && (int) ($product->stock ?? 0) > 0)
                In Stock
            @else
                Out of Stock
            @endif
        </span>
    </div>

    @if (($product->stock_status ?? 'in_stock') === 'in_stock' && (int) ($product->stock ?? 0) > 0)
        @php
            $qtyMin = max(1, (int) ($product->qty_min ?? 1));
            $qtyMax = max($qtyMin, (int) ($product->qty_max ?? $product->stock ?? 1));
        @endphp
        <div class="qty-row">
            <div class="qty-control" role="group" aria-label="Quantity">
                <button type="button" data-qty-minus aria-label="Decrease quantity" disabled>−</button>
                <input
                    type="number"
                    name="quantity"
                    value="{{ $qtyMin }}"
                    min="{{ $qtyMin }}"
                    max="{{ $qtyMax }}"
                    step="1"
                    inputmode="numeric"
                    data-qty-input
                    aria-label="Quantity"
                >
                <button type="button" data-qty-plus aria-label="Increase quantity" @disabled($qtyMin >= $qtyMax)>+</button>
            </div>
            <span class="visually-hidden">Select quantity between {{ $qtyMin }} and {{ $qtyMax }}</span>
        </div>

        <div class="product-actions">
            <button type="button" class="btn-add-cart-lg" data-add-to-cart>
                <i class="bi bi-bag" aria-hidden="true"></i>
                Add to Cart
            </button>
            <button type="button" class="btn-buy-now" data-buy-now>
                Buy Now
            </button>
        </div>
    @else
        <p class="stock-unavailable-note">This piece is currently unavailable. Get an email when it returns, or browse similar jewellery.</p>
        <div class="product-actions">
            <button type="button" class="btn-add-cart-lg" data-stock-notify data-no-loading data-product-id="{{ $product->id }}">
                <i class="bi bi-bell" aria-hidden="true"></i>
                Notify me
            </button>
            <a class="btn-buy-now" href="{{ $product->similar_url ?? route('products.new-arrivals') }}">
                View similar
            </a>
        </div>
    @endif

    <div class="delivery-card">
        <h3>Check Delivery</h3>
        <p>Enter your pincode to check availability and estimated delivery.</p>
        <form class="delivery-form" action="{{ route('delivery.check') }}" method="post" data-delivery-form data-no-loading novalidate>
            @csrf
            <label class="visually-hidden" for="pincode">Pincode</label>
            <input id="pincode" type="text" name="pincode" placeholder="6-digit pincode" maxlength="6" inputmode="numeric" autocomplete="postal-code" required>
            <button type="submit" data-delivery-submit data-no-loading>Check</button>
        </form>
        <div class="delivery-result" data-delivery-result aria-live="polite"></div>
    </div>

    <div class="product-mini-services" aria-label="Purchase benefits">
        <div class="item">
            <i class="bi bi-box-seam" aria-hidden="true"></i>
            <strong>Secure Packaging</strong>
            <span>Packed with care</span>
        </div>
        <div class="item">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            <strong>Easy Returns</strong>
            <span>15 Day Return Policy</span>
        </div>
        <div class="item">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
            <strong>Secure Payment</strong>
            <span>100% Safe & Secure</span>
        </div>
        <div class="item">
            <i class="bi bi-stars" aria-hidden="true"></i>
            <strong>Quality-checked</strong>
            <span>Premium finish</span>
        </div>
    </div>
</div>
