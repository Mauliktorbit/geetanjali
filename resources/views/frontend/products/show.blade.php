@extends('frontend.layouts.app')

@section('title', $product->name . ' | ' . config('brand.name'))
@section('meta_description', $product->short_description)

@section('content')
    <div
        class="product-page"
        data-product-page
        data-product-id="{{ $product->id }}"
        data-product-name="{{ $product->name }}"
        data-product-price="{{ $product->sale_price ?: $product->price }}"
        data-product-image="{{ storefront_image($product->images[0]->url ?? $product->image ?? null) }}"
        data-product-url="{{ url()->current() }}"
        data-product-slug="{{ $product->slug }}"
        data-product-metal="{{ $product->metal ?? '' }}"
        data-product-weight="{{ $product->weight ?? '' }}"
        data-stock="{{ $product->stock }}"
        data-qty-min="{{ max(1, (int) ($product->qty_min ?? 1)) }}"
        data-qty-max="{{ max(1, (int) ($product->qty_max ?? $product->stock ?? 1)) }}"
        @if (($product->stock_status ?? '') === 'out_of_stock' || (int) $product->stock <= 0) data-out-of-stock="1" @endif
        data-cart-url="{{ route('cart.add') }}"
        data-checkout-url="{{ route('checkout.index') }}"
    >
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="product-shell">
            <div class="product-main">
                @include('frontend.components.product.gallery', ['product' => $product])
                @include('frontend.components.product.info', ['product' => $product])
            </div>

            @include('frontend.components.product.tabs', ['product' => $product])
            @include('frontend.components.product.related', [
                'products' => $relatedProducts,
                'title' => $product->category ? 'Similar '.$product->category : 'You May Also Like',
                'viewAllUrl' => $product->similar_url ?? route('products.new-arrivals'),
            ])
            @include('frontend.components.product.related', [
                'products' => $recentlyViewed ?? [],
                'title' => 'Recently Viewed',
            ])
            @include('frontend.components.product.related', [
                'products' => $trendingProducts ?? [],
                'title' => $product->category ? 'More '.$product->category : 'More like this',
                'viewAllUrl' => $product->similar_url ?? route('products.new-arrivals'),
            ])
        </div>

        @include('frontend.components.product.service-strip')
        @include('frontend.components.product.lightbox', ['product' => $product])
    </div>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => collect($product->images)->map(fn ($img) => storefront_image($img->url ?? null))->values()->all(),
            'description' => $product->short_description,
            'sku' => $product->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => config('brand.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'INR',
                'price' => $product->sale_price ?: $product->price,
                'availability' => (($product->stock_status ?? 'in_stock') === 'in_stock' && (int) $product->stock > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->rating,
                'reviewCount' => $product->review_count,
            ],
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
    </script>
@endsection
