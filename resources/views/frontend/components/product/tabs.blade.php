@props(['product'])

<section class="product-tabs-section" id="product-tabs" aria-label="Product information">
    <div class="product-tabs" role="tablist" aria-label="Product details tabs">
        <button type="button" role="tab" class="is-active" data-product-tab="description" aria-selected="true">Description</button>
        <button type="button" role="tab" data-product-tab="details" aria-selected="false">Product Details</button>
        <button type="button" role="tab" data-product-tab="shipping" aria-selected="false">Shipping & Returns</button>
        <button type="button" role="tab" data-product-tab="care" aria-selected="false">Care Instructions</button>
        <button type="button" role="tab" data-product-tab="reviews" aria-selected="false">Reviews ({{ $product->review_count }})</button>
    </div>

    <div class="product-tab-panels">
        <div class="tab-panel is-active" data-product-panel="description" role="tabpanel">
            <div class="tab-grid">
                <div class="tab-copy">
                    <p>{{ $product->description }}</p>
                    <ul class="check-list">
                        @foreach ($product->highlights ?? [] as $item)
                            <li>
                                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <aside class="why-card">
                    <h3>Why Choose Geetanjali Jewellers?</h3>
                    <ul>
                        <li><i class="bi bi-award" aria-hidden="true"></i><span>100% Hallmarked & Certified Jewellery</span></li>
                        <li><i class="bi bi-stars" aria-hidden="true"></i><span>Exquisite Craftsmanship</span></li>
                        <li><i class="bi bi-people" aria-hidden="true"></i><span>Trusted by 1L+ Happy Customers</span></li>
                        <li><i class="bi bi-headset" aria-hidden="true"></i><span>Lifetime Maintenance & Support</span></li>
                    </ul>
                </aside>
            </div>
        </div>

        <div class="tab-panel" data-product-panel="details" role="tabpanel">
            <table class="details-table">
                <tbody>
                    <tr><td>SKU</td><td>{{ $product->sku }}</td></tr>
                    <tr><td>Category</td><td>{{ $product->category }}</td></tr>
                    <tr><td>Metal</td><td>{{ $product->metal }}</td></tr>
                    <tr><td>Purity</td><td>{{ $product->purity }}</td></tr>
                    <tr><td>Stone</td><td>{{ $product->stone }}</td></tr>
                    <tr><td>Weight</td><td>{{ $product->weight }}</td></tr>
                    <tr><td>Dimensions</td><td>{{ $product->dimensions }}</td></tr>
                    <tr><td>Occasion</td><td>{{ $product->occasion }}</td></tr>
                    <tr><td>Style</td><td>{{ $product->style }}</td></tr>
                    <tr><td>Certification</td><td>{{ $product->certification }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="tab-panel" data-product-panel="shipping" role="tabpanel">
            <ul class="check-list">
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Free shipping on eligible orders</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Estimated delivery in 3–5 business days</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Easy 15-day returns on unused jewellery</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Exchange available as per store policy</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Insured delivery for complete peace of mind</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Premium gift packaging on request</span></li>
            </ul>
        </div>

        <div class="tab-panel" data-product-panel="care" role="tabpanel">
            <ul class="check-list">
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Keep away from chemicals, perfumes and sprays</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Store separately in a soft jewellery pouch or box</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Avoid water and moisture exposure</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Wipe gently with a soft dry cloth after use</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Professional cleaning recommended periodically</span></li>
            </ul>
        </div>

        <div class="tab-panel" data-product-panel="reviews" role="tabpanel">
            @php
                $totalReviews = max(1, (int) $product->review_count);
                $breakdown = $product->rating_breakdown ?? [];
            @endphp
            <div class="review-summary">
                <div class="review-score">
                    <div class="big">{{ number_format($product->rating, 1) }}</div>
                    <div class="product-stars" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star-fill"></i>
                        @endfor
                    </div>
                    <div class="meta">{{ $product->review_count }} Reviews</div>
                </div>
                <div>
                    @for ($star = 5; $star >= 1; $star--)
                        @php $count = (int) ($breakdown[$star] ?? 0); @endphp
                        <div class="bar-row">
                            <span>{{ $star }} Stars</span>
                            <div class="bar-track"><div class="bar-fill" style="width: {{ ($count / $totalReviews) * 100 }}%"></div></div>
                            <span>{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            @foreach ($product->reviews as $review)
                <article class="review-card">
                    <strong>{{ $review->name }}</strong>
                    <div class="meta">
                        <span class="product-stars" aria-label="{{ $review->rating }} stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                            @endfor
                        </span>
                        · {{ $review->date }}
                    </div>
                    <p>{{ $review->text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
