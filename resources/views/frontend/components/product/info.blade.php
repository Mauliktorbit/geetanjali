@props(['product'])

<div class="product-info">
    <h1 class="product-title">{{ $product->name }}</h1>

    <div class="product-rating-row">
        <span class="product-stars" aria-label="{{ $product->rating }} out of 5 stars">
            @for ($i = 1; $i <= 5; $i++)
                <i class="bi {{ $i <= floor($product->rating) ? 'bi-star-fill' : ($i - $product->rating < 1 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
            @endfor
        </span>
        <strong>{{ number_format($product->rating, 1) }}</strong>
        <a href="#product-tabs" data-open-reviews>({{ $product->review_count }} Reviews)</a>
        <span class="sep" aria-hidden="true">|</span>
        <span>Sold {{ $product->sold_count }}</span>
    </div>

    <p class="product-price">₹{{ number_format($product->sale_price ?: $product->price) }}</p>
    @if (!empty($product->sale_price) && $product->sale_price < $product->price)
        <p class="product-price-note">
            <span style="text-decoration:line-through;margin-right:6px;">₹{{ number_format($product->price) }}</span>
            {{ $product->tax_note }}
        </p>
    @else
        <p class="product-price-note">{{ $product->tax_note }}</p>
    @endif

    <p class="product-short">{{ $product->short_description }}</p>

    <div class="product-benefits" aria-label="Product benefits">
        @foreach ($product->benefits as $benefit)
            <div class="product-benefit">
                <i class="bi {{ $benefit['icon'] }}" aria-hidden="true"></i>
                <span>{{ $benefit['label'] }}</span>
            </div>
        @endforeach
    </div>

    <p class="attr-label-top">Metal</p>
    <div class="metal-pill">{{ $product->metal }}</div>

    <ul class="product-attrs">
        <li><span class="label">Stone:</span><span class="value">{{ $product->stone }}</span></li>
        <li><span class="label">Style:</span><span class="value">{{ $product->style }}</span></li>
        <li><span class="label">Net Weight:</span><span class="value">{{ $product->weight }}</span></li>
    </ul>

    <div class="stock-status {{ ($product->stock_status ?? '') === 'out_of_stock' ? 'is-out' : '' }}">
        <span class="stock-dot" aria-hidden="true"></span>
        <span>
            @if (($product->stock_status ?? 'in_stock') === 'in_stock')
                In Stock
            @else
                Out of Stock
            @endif
        </span>
    </div>

    <div class="qty-row">
        <div class="qty-control" role="group" aria-label="Quantity">
            <button type="button" data-qty-minus aria-label="Decrease quantity">−</button>
            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" data-qty-input aria-label="Quantity">
            <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
        </div>
        <span class="visually-hidden">Select quantity</span>
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

    <div class="delivery-card">
        <h3>Check Delivery</h3>
        <p>Enter your pincode to check availability and estimated delivery.</p>
        <form class="delivery-form" action="{{ route('delivery.check') }}" method="post" data-delivery-form>
            @csrf
            <label class="visually-hidden" for="pincode">Pincode</label>
            <input id="pincode" type="text" name="pincode" placeholder="Enter Pincode" maxlength="10" required>
            <button type="submit">Check</button>
        </form>
        <div class="delivery-result" data-delivery-result aria-live="polite"></div>
    </div>

    <div class="product-mini-services" aria-label="Purchase benefits">
        <div class="item">
            <i class="bi bi-truck" aria-hidden="true"></i>
            <strong>Free Shipping</strong>
            <span>On All Orders</span>
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
            <i class="bi bi-gem" aria-hidden="true"></i>
            <strong>Lifetime Service</strong>
            <span>Maintenance & Repair</span>
        </div>
    </div>
</div>
