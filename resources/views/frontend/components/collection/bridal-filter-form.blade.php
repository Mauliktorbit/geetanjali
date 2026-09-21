@props([
    'filters' => [],
    'formId' => 'bridal-filter-form',
    'showSort' => true,
    'compact' => true,
    'listingUrl' => null,
    'showBridalSets' => true,
    'filterCounts' => [],
])

@php
    $view = $filters['view'] ?? 'grid';
    $categoryOptions = $categoryOptions ?? \App\Services\StorefrontCatalogService::jewelleryTypeOptions();
    $listingUrl = $listingUrl ?? route('collections.bridal');
    $typeCounts = $filterCounts['type'] ?? [];
    $metalCounts = $filterCounts['metal'] ?? [];
    $stoneCounts = $filterCounts['stone'] ?? [];
    $listingTotal = (int) ($filterCounts['total'] ?? 0);
    $bridalSetsCount = (int) ($typeCounts['sets'] ?? 0) + (int) ($typeCounts['bridal'] ?? 0);
    $selectedCategory = (string) ($filters['category'] ?? '');
    $selectedMetal = (string) ($filters['metal'] ?? '');
    $selectedStone = (string) ($filters['stone'] ?? '');
@endphp

<form
    id="{{ $formId }}"
    class="bridal-filter-form {{ $compact ? 'bridal-filter-form--compact' : 'bridal-filter-form--stacked' }}"
    method="get"
    action="{{ $listingUrl }}"
    data-bridal-filter-form
>
    <div class="bridal-filter-form__filters">
        <label class="visually-hidden" for="{{ $formId }}-category">Category</label>
        <select id="{{ $formId }}-category" name="category" class="bridal-select" @if ($compact) data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()" @endif>
            <option value="" @selected($selectedCategory === '')>All Categories{{ $listingTotal > 0 ? ' ('.$listingTotal.')' : '' }}</option>
            @if ($showBridalSets && ($bridalSetsCount > 0 || $selectedCategory === 'bridal-sets'))
                <option value="bridal-sets" @selected($selectedCategory === 'bridal-sets')>Bridal Sets ({{ $bridalSetsCount }})</option>
            @endif
            @foreach ($categoryOptions as $key => $label)
                @php $count = (int) ($typeCounts[$key] ?? 0); @endphp
                @if ($key === 'sets')
                    @if (! $showBridalSets && ($count > 0 || $selectedCategory === 'sets'))
                        <option value="sets" @selected($selectedCategory === 'sets')>{{ $label }} ({{ $count }})</option>
                    @endif
                    @continue
                @endif
                @if ($count > 0 || $selectedCategory === $key)
                    <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }} ({{ $count }})</option>
                @endif
            @endforeach
            @if (! $showBridalSets && ! isset($categoryOptions['sets']) && (((int) ($typeCounts['sets'] ?? 0)) > 0 || $selectedCategory === 'sets'))
                <option value="sets" @selected($selectedCategory === 'sets')>Sets ({{ (int) ($typeCounts['sets'] ?? 0) }})</option>
            @endif
        </select>

        <label class="visually-hidden" for="{{ $formId }}-metal">Finish</label>
        <select id="{{ $formId }}-metal" name="metal" class="bridal-select" @if ($compact) data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()" @endif>
            <option value="" @selected(($filters['metal'] ?? '') === '')>All Finishes</option>
            @foreach (\App\Services\StorefrontCatalogService::METAL_LABELS as $key => $label)
                @php $count = (int) ($metalCounts[$key] ?? 0); @endphp
                @if ($count > 0 || $selectedMetal === $key)
                    <option value="{{ $key }}" @selected($selectedMetal === $key)>{{ $label }}{{ $count > 0 ? ' ('.$count.')' : '' }}</option>
                @endif
            @endforeach
        </select>

        <label class="visually-hidden" for="{{ $formId }}-stone">Stone</label>
        <select id="{{ $formId }}-stone" name="stone" class="bridal-select" @if ($compact) data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()" @endif>
            <option value="" @selected(($filters['stone'] ?? '') === '')>All Stones</option>
            @foreach (['kundan' => 'Kundan', 'emerald' => 'Emerald', 'ruby' => 'Ruby', 'diamond' => 'Diamond', 'pearl' => 'Pearl'] as $key => $label)
                @php $count = (int) ($stoneCounts[$key] ?? 0); @endphp
                @if ($count > 0 || $selectedStone === $key)
                    <option value="{{ $key }}" @selected($selectedStone === $key)>{{ $label }}{{ $count > 0 ? ' ('.$count.')' : '' }}</option>
                @endif
            @endforeach
        </select>

        <label class="visually-hidden" for="{{ $formId }}-price">Price range</label>
        <select id="{{ $formId }}-price" name="price" class="bridal-select" @if ($compact) data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()" @endif>
            <option value="" @selected(($filters['price'] ?? '') === '')>Price Range</option>
            <option value="under-50000" @selected(($filters['price'] ?? '') === 'under-50000')>Under ₹50,000</option>
            <option value="50000-100000" @selected(($filters['price'] ?? '') === '50000-100000')>₹50,000 – ₹1,00,000</option>
            <option value="100000-200000" @selected(($filters['price'] ?? '') === '100000-200000')>₹1,00,000 – ₹2,00,000</option>
            <option value="200000-plus" @selected(($filters['price'] ?? '') === '200000-plus')>₹2,00,000+</option>
        </select>
    </div>

    <div class="bridal-filter-form__actions">
        @if ($showSort)
            <div class="bridal-sort">
                <label for="{{ $formId }}-sort">Sort By:</label>
                <select id="{{ $formId }}-sort" name="sort" class="bridal-select bridal-select--sort" data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()">
                    <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>Featured</option>
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                    <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                    <option value="bestselling" @selected(($filters['sort'] ?? '') === 'bestselling')>Best Selling</option>
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest Rated</option>
                </select>
            </div>
        @endif

        <input type="hidden" name="view" value="{{ $view }}" data-view-input>
        @include('frontend.components.collection.view-toggle', ['view' => $view])

        @unless ($compact)
            <button type="submit" class="bridal-btn bridal-btn--apply">Apply Filters</button>
            <a href="{{ $listingUrl }}" class="bridal-filter-clear">Clear All</a>
        @endunless
    </div>
</form>
