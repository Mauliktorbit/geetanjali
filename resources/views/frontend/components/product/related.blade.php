@props([
    'products' => [],
    'title' => 'You May Also Like',
    'viewAllUrl' => null,
])

@php
    $items = collect($products)->filter()->values();
    $viewAllUrl = $viewAllUrl ?: route('products.new-arrivals');
@endphp

@if ($items->isNotEmpty())
<section class="related-section" aria-labelledby="{{ \Illuminate\Support\Str::slug($title) }}-heading">
    <div class="related-head">
        <h2 id="{{ \Illuminate\Support\Str::slug($title) }}-heading">{{ $title }}</h2>
        <a href="{{ $viewAllUrl }}">View All →</a>
    </div>

    <div class="related-product-grid">
        @foreach ($items as $item)
            @include('frontend.components.product-card', [
                'product' => is_array($item) ? $item : (array) $item,
                'showCart' => false,
            ])
        @endforeach
    </div>
</section>
@endif
