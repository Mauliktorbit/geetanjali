@props([
    'listingUrl' => '',
    'filters' => [],
    'products' => null,
    'emptyMessage' => 'No products match your filters.',
    'formPrefix' => 'collection',
    'heading' => null,
    'showBridalSets' => false,
    'filterCounts' => [],
])

@php
    $view = $filters['view'] ?? 'grid';
    $formId = $formPrefix.'-filter-form';
    $drawerId = $formPrefix.'FilterDrawer';
@endphp

@include('frontend.components.collection.bridal-toolbar', [
    'filters' => $filters,
    'listingUrl' => $listingUrl,
    'showBridalSets' => $showBridalSets,
    'filterCounts' => $filterCounts,
    'formPrefix' => $formPrefix,
    'drawerId' => $drawerId,
])

<div class="bridal-listing__meta">
    @if ($heading)
        <h2 class="font-heading">{{ $heading }}</h2>
    @endif
    <p class="bridal-listing__count">
        @if ($products && $products->total() > 0)
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
        @else
            Showing 0 products
        @endif
    </p>
</div>

@if ($products && $products->count() > 0)
    <div class="product-grid bridal-grid{{ $view === 'list' ? ' bridal-grid--list' : '' }}" data-bridal-grid>
        @foreach ($products as $product)
            @include('frontend.components.product-card', ['product' => $product, 'showCart' => false])
        @endforeach
    </div>

    <div class="bridal-pagination">
        {{ $products->fragment('collection-products')->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="bridal-empty">
        <p>{{ $emptyMessage }}</p>
        <a href="{{ $listingUrl }}" class="bridal-btn bridal-btn--apply">Clear All</a>
    </div>
@endif

<div class="bridal-filter-drawer" id="{{ $drawerId }}" data-bridal-drawer hidden>
    <div class="bridal-filter-drawer__backdrop" data-bridal-drawer-close tabindex="-1"></div>
    <div class="bridal-filter-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="{{ $formPrefix }}-filter-drawer-title">
        <div class="bridal-filter-drawer__head">
            <h2 id="{{ $formPrefix }}-filter-drawer-title">Filters</h2>
            <button type="button" class="bridal-filter-drawer__close" data-bridal-drawer-close aria-label="Close filters">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="bridal-filter-drawer__body">
            @include('frontend.components.collection.bridal-filter-form', [
                'filters' => $filters,
                'formId' => $formId.'-mobile',
                'showSort' => false,
                'compact' => false,
                'listingUrl' => $listingUrl,
                'showBridalSets' => $showBridalSets,
                'filterCounts' => $filterCounts,
            ])
        </div>
    </div>
</div>
