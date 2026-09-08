@props([
    'filters' => [],
    'formId' => 'bridal-filter-form',
    'showSort' => true,
    'compact' => true,
])

@php
    $view = $filters['view'] ?? 'grid';
@endphp

<form
    id="{{ $formId }}"
    class="bridal-filter-form {{ $compact ? 'bridal-filter-form--compact' : 'bridal-filter-form--stacked' }}"
    method="get"
    action="{{ route('collections.bridal') }}"
    data-bridal-filter-form
>
    <div class="bridal-filter-form__filters">
        <label class="visually-hidden" for="{{ $formId }}-category">Category</label>
        <select id="{{ $formId }}-category" name="category" class="bridal-select" @if ($compact) data-auto-submit @endif>
            <option value="" @selected(($filters['category'] ?? '') === '')>All Categories</option>
            <option value="bridal-sets" @selected(($filters['category'] ?? '') === 'bridal-sets')>Bridal Sets</option>
            <option value="necklaces" @selected(($filters['category'] ?? '') === 'necklaces')>Necklaces</option>
            <option value="earrings" @selected(($filters['category'] ?? '') === 'earrings')>Earrings</option>
            <option value="bangles" @selected(($filters['category'] ?? '') === 'bangles')>Bangles</option>
            <option value="rings" @selected(($filters['category'] ?? '') === 'rings')>Rings</option>
            <option value="maang-tikka" @selected(($filters['category'] ?? '') === 'maang-tikka')>Maang Tikka</option>
            <option value="jhumkas" @selected(($filters['category'] ?? '') === 'jhumkas')>Jhumkas</option>
        </select>

        <label class="visually-hidden" for="{{ $formId }}-metal">Metal</label>
        <select id="{{ $formId }}-metal" name="metal" class="bridal-select" @if ($compact) data-auto-submit @endif>
            <option value="" @selected(($filters['metal'] ?? '') === '')>All Metals</option>
            <option value="22k" @selected(($filters['metal'] ?? '') === '22k')>22K Gold</option>
            <option value="18k" @selected(($filters['metal'] ?? '') === '18k')>18K Gold</option>
            <option value="diamond" @selected(($filters['metal'] ?? '') === 'diamond')>Diamond</option>
            <option value="platinum" @selected(($filters['metal'] ?? '') === 'platinum')>Platinum</option>
        </select>

        <label class="visually-hidden" for="{{ $formId }}-stone">Stone</label>
        <select id="{{ $formId }}-stone" name="stone" class="bridal-select" @if ($compact) data-auto-submit @endif>
            <option value="" @selected(($filters['stone'] ?? '') === '')>All Stones</option>
            <option value="kundan" @selected(($filters['stone'] ?? '') === 'kundan')>Kundan</option>
            <option value="emerald" @selected(($filters['stone'] ?? '') === 'emerald')>Emerald</option>
            <option value="ruby" @selected(($filters['stone'] ?? '') === 'ruby')>Ruby</option>
            <option value="diamond" @selected(($filters['stone'] ?? '') === 'diamond')>Diamond</option>
            <option value="pearl" @selected(($filters['stone'] ?? '') === 'pearl')>Pearl</option>
        </select>

        <label class="visually-hidden" for="{{ $formId }}-price">Price range</label>
        <select id="{{ $formId }}-price" name="price" class="bridal-select" @if ($compact) data-auto-submit @endif>
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
                <select id="{{ $formId }}-sort" name="sort" class="bridal-select bridal-select--sort" @if ($compact) data-auto-submit @endif>
                    <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>Featured</option>
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                    <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                    <option value="bestselling" @selected(($filters['sort'] ?? '') === 'bestselling')>Best Selling</option>
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest Rated</option>
                </select>
            </div>
        @endif

        <div class="bridal-view-toggle" role="group" aria-label="Product view">
            <input type="hidden" name="view" value="{{ $view }}" data-view-input>
            <button
                type="button"
                class="bridal-view-btn {{ $view === 'grid' ? 'is-active' : '' }}"
                data-view="grid"
                aria-label="Grid view"
                aria-pressed="{{ $view === 'grid' ? 'true' : 'false' }}"
            >
                <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
            </button>
            <button
                type="button"
                class="bridal-view-btn {{ $view === 'list' ? 'is-active' : '' }}"
                data-view="list"
                aria-label="List view"
                aria-pressed="{{ $view === 'list' ? 'true' : 'false' }}"
            >
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
        </div>

        @unless ($compact)
            <button type="submit" class="bridal-btn bridal-btn--apply">Apply Filters</button>
            <a href="{{ route('collections.bridal') }}" class="bridal-filter-clear">Clear All</a>
        @endunless
    </div>
</form>
