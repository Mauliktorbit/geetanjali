@props(['filters' => [], 'listingUrl' => null, 'showBridalSets' => true])

@php
    $listingUrl = $listingUrl ?? route('collections.bridal');
@endphp

<div class="bridal-toolbar">
    <div class="bridal-toolbar__mobile d-lg-none">
        <button type="button" class="bridal-toolbar__chip" data-bridal-drawer-open aria-controls="bridalFilterDrawer" aria-expanded="false">
            <i class="bi bi-sliders" aria-hidden="true"></i>
            Filter
        </button>
        @include('frontend.components.collection.view-toggle', ['view' => $filters['view'] ?? 'grid'])
        <form method="get" action="{{ $listingUrl }}" class="bridal-toolbar__sort-mobile">
            @foreach (['category', 'metal', 'stone', 'price'] as $key)
                @if (!empty($filters[$key]))
                    <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
                @endif
            @endforeach
            <input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}">
            <label class="visually-hidden" for="bridal-sort-mobile">Sort by</label>
            <select id="bridal-sort-mobile" name="sort" class="bridal-select" data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()">
                <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>Featured</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                <option value="bestselling" @selected(($filters['sort'] ?? '') === 'bestselling')>Best Selling</option>
                <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest Rated</option>
            </select>
        </form>
    </div>

    <div class="bridal-toolbar__desktop d-none d-lg-flex">
        @include('frontend.components.collection.bridal-filter-form', [
            'filters' => $filters,
            'formId' => 'bridal-filter-form',
            'showSort' => true,
            'compact' => true,
            'listingUrl' => $listingUrl,
            'showBridalSets' => $showBridalSets,
        ])
    </div>
</div>
