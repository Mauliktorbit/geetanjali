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

                @if (!empty($product->about_points))
                <aside class="why-card">
                    <h3>About this piece</h3>
                    <ul>
                        @foreach ($product->about_points as $point)
                            <li>
                                <i class="bi {{ $point['icon'] ?? 'bi-check-circle' }}" aria-hidden="true"></i>
                                <span>{{ $point['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </aside>
                @endif
            </div>
        </div>

        <div class="tab-panel" data-product-panel="details" role="tabpanel">
            <table class="details-table">
                <tbody>
                    <tr><td>SKU</td><td>{{ $product->sku }}</td></tr>
                    <tr><td>Category</td><td>{{ $product->category }}</td></tr>
                    @if (filled($product->metal))
                        <tr><td>Material</td><td>{{ $product->metal }}</td></tr>
                    @endif
                    @if (filled($product->stone))
                        <tr><td>Stone</td><td>{{ $product->stone }}</td></tr>
                    @endif
                    @if (filled($product->weight))
                        <tr><td>Weight</td><td>{{ $product->weight }}</td></tr>
                    @endif
                    @if (filled($product->dimensions))
                        <tr><td>Dimensions</td><td>{{ $product->dimensions }}</td></tr>
                    @endif
                    @if (filled($product->occasion))
                        <tr><td>Occasion</td><td>{{ $product->occasion }}</td></tr>
                    @endif
                    @if (filled($product->style))
                        <tr><td>Style</td><td>{{ $product->style }}</td></tr>
                    @endif
                    @if (filled($product->certification))
                        <tr><td>Quality</td><td>{{ $product->certification }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="tab-panel" data-product-panel="shipping" role="tabpanel">
            <ul class="check-list">
                @php
                    $shippingItems = $product->shipping_information ?? [];
                    $returnItems = $product->return_policy ?? [];
                    if ($shippingItems === [] && $returnItems === []) {
                        $shippingItems = [
                            'Shipping charges calculated at checkout',
                            'Estimated delivery in '.($product->estimated_delivery ?: '3–5 business days'),
                            'Insured delivery for complete peace of mind',
                            'Premium gift packaging on request',
                        ];
                        $returnItems = [
                            'Easy 15-day returns on unused jewellery',
                            'Exchange available as per store policy',
                        ];
                    } else {
                        $hasEta = collect($shippingItems)->contains(
                            fn ($line) => str_contains(strtolower((string) $line), 'deliver')
                        );
                        if (! $hasEta) {
                            array_unshift(
                                $shippingItems,
                                'Estimated delivery in '.($product->estimated_delivery ?: '3–5 business days')
                            );
                        }
                    }
                @endphp
                @foreach (array_merge($shippingItems, $returnItems) as $item)
                    @if (filled($item))
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ $item }}</span></li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div class="tab-panel" data-product-panel="care" role="tabpanel">
            <ul class="check-list">
                @php
                    $careItems = $product->care_instructions ?? [];
                    if ($careItems === []) {
                        $careItems = [
                            'Keep away from perfumes, sprays and household chemicals',
                            'Store in a dry pouch, away from other jewellery',
                            'Avoid water, sweat and prolonged moisture',
                            'Wipe gently with a soft dry cloth after wearing',
                            'Do not use jewellery cleaning dips or ultrasonic cleaners',
                        ];
                    }
                @endphp
                @foreach ($careItems as $item)
                    <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ $item }}</span></li>
                @endforeach
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
                    <div class="meta">{{ review_count_label($product->review_count) }}</div>
                </div>
                <div>
                    @for ($star = 5; $star >= 1; $star--)
                        @php $count = (int) ($breakdown[$star] ?? 0); @endphp
                        <div class="bar-row">
                            <span>{{ $star }} {{ $star === 1 ? 'Star' : 'Stars' }}</span>
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
