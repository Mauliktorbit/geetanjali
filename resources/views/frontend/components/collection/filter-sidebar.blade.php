@props([
    'filters' => [],
    'filterCounts' => [],
    'minPriceBound' => 2000,
    'maxPriceBound' => 500000,
    'formId' => 'kundan-filter-form',
])

@php
    $typeOptions = $typeOptions ?? \App\Services\StorefrontCatalogService::jewelleryTypeOptions();
    $metalOptions = [
        '22k' => '22K Yellow Gold',
        '18k' => '18K Gold',
    ];
    $stoneOptions = [
        'emerald' => 'Emerald',
        'ruby' => 'Ruby',
        'pearl' => 'Pearl',
        'polki' => 'Polki',
        'meenakari' => 'Meenakari',
    ];
    $selectedTypes = $filters['type'] ?? [];
    $selectedMetals = $filters['metal'] ?? [];
    $selectedStones = $filters['stone'] ?? [];
    $minPrice = $filters['min_price'] ?? $minPriceBound;
    $maxPrice = $filters['max_price'] ?? $maxPriceBound;
    $sort = $filters['sort'] ?? 'popularity';
@endphp

<form
    id="{{ $formId }}"
    class="kundan-filters"
    method="get"
    action="{{ route('collections.kundan') }}"
    data-filter-form
>
    <input type="hidden" name="sort" value="{{ $sort }}">

    <div class="kundan-filters__head">
        <h3>Filters</h3>
        <a href="{{ route('collections.kundan') }}" class="kundan-filters__clear">Clear All</a>
    </div>

    <fieldset class="kundan-filters__group">
        <legend>Jewellery Type</legend>
        @foreach ($typeOptions as $key => $label)
            <label class="kundan-check">
                <input
                    type="checkbox"
                    name="type[]"
                    value="{{ $key }}"
                    @checked(in_array($key, $selectedTypes, true))
                >
                <span>{{ $label }} ({{ $filterCounts['type'][$key] ?? 0 }})</span>
            </label>
        @endforeach
    </fieldset>

    <fieldset class="kundan-filters__group" data-price-range>
        <legend>Price Range</legend>
        <div class="kundan-price-values">
            <span data-price-min-label>₹{{ number_format($minPrice) }}</span>
            <span data-price-max-label>₹{{ number_format($maxPrice) }}</span>
        </div>
        <div class="kundan-price-slider">
            <div class="kundan-price-slider__track" aria-hidden="true">
                <div class="kundan-price-slider__range" data-price-range-fill></div>
            </div>
            <label class="visually-hidden" for="{{ $formId }}-min">Minimum price</label>
            <input
                id="{{ $formId }}-min"
                type="range"
                name="min_price"
                min="{{ $minPriceBound }}"
                max="{{ $maxPriceBound }}"
                step="1000"
                value="{{ $minPrice }}"
                data-price-min
            >
            <label class="visually-hidden" for="{{ $formId }}-max">Maximum price</label>
            <input
                id="{{ $formId }}-max"
                type="range"
                name="max_price"
                min="{{ $minPriceBound }}"
                max="{{ $maxPriceBound }}"
                step="1000"
                value="{{ $maxPrice }}"
                data-price-max
            >
        </div>
    </fieldset>

    <fieldset class="kundan-filters__group">
        <legend>Metal</legend>
        @foreach ($metalOptions as $key => $label)
            <label class="kundan-check">
                <input
                    type="checkbox"
                    name="metal[]"
                    value="{{ $key }}"
                    @checked(in_array($key, $selectedMetals, true))
                >
                <span>{{ $label }} ({{ $filterCounts['metal'][$key] ?? 0 }})</span>
            </label>
        @endforeach
    </fieldset>

    <fieldset class="kundan-filters__group">
        <legend>Stone</legend>
        @foreach ($stoneOptions as $key => $label)
            <label class="kundan-check">
                <input
                    type="checkbox"
                    name="stone[]"
                    value="{{ $key }}"
                    @checked(in_array($key, $selectedStones, true))
                >
                <span>{{ $label }} ({{ $filterCounts['stone'][$key] ?? 0 }})</span>
            </label>
        @endforeach
    </fieldset>

    <button type="submit" class="btn-apply-filters">Apply Filters</button>
</form>
