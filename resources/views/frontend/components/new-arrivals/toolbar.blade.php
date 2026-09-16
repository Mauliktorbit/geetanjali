@props([
    'filters' => [],
    'products' => null,
])

<div class="na-toolbar">
    <div class="na-toolbar__left">
        <button
            type="button"
            class="na-filter-btn"
            data-na-drawer-open
            aria-controls="naFilterDrawer"
            aria-expanded="false"
        >
            <i class="bi bi-funnel" aria-hidden="true"></i>
            Filter
        </button>
    </div>

    <p class="na-toolbar__count">
        @if ($products && $products->total() > 0)
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} Products
        @else
            Showing 0 Products
        @endif
    </p>

    <div class="na-toolbar__right">
        <form method="get" action="{{ route('products.new-arrivals') }}" class="na-sort-form" data-na-sort-form>
            @foreach (($filters['category'] ?? []) as $value)
                <input type="hidden" name="category[]" value="{{ $value }}">
            @endforeach
            @foreach (($filters['metal'] ?? []) as $value)
                <input type="hidden" name="metal[]" value="{{ $value }}">
            @endforeach
            @foreach (($filters['stone'] ?? []) as $value)
                <input type="hidden" name="stone[]" value="{{ $value }}">
            @endforeach
            @foreach (($filters['occasion'] ?? []) as $value)
                <input type="hidden" name="occasion[]" value="{{ $value }}">
            @endforeach
            @if (!empty($filters['min_price']))
                <input type="hidden" name="min_price" value="{{ $filters['min_price'] }}">
            @endif
            @if (!empty($filters['max_price']))
                <input type="hidden" name="max_price" value="{{ $filters['max_price'] }}">
            @endif
            <input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}" data-view-input>

            <label for="na-sort" class="na-sort-label">Sort by:</label>
            <select id="na-sort" name="sort" class="na-select" data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()">
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest First</option>
                <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Price: Low to High</option>
                <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>Price: High to Low</option>
                <option value="popularity" @selected(($filters['sort'] ?? '') === 'popularity')>Popularity</option>
                <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Rating</option>
                <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>Name: A-Z</option>
                <option value="name_desc" @selected(($filters['sort'] ?? '') === 'name_desc')>Name: Z-A</option>
            </select>

            <div class="na-view-toggle" role="group" aria-label="Product view">
                <button
                    type="button"
                    class="na-view-btn {{ ($filters['view'] ?? 'grid') === 'grid' ? 'is-active' : '' }}"
                    data-view="grid"
                    aria-label="Grid view"
                    aria-pressed="{{ ($filters['view'] ?? 'grid') === 'grid' ? 'true' : 'false' }}"
                >
                    <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
                </button>
                <button
                    type="button"
                    class="na-view-btn {{ ($filters['view'] ?? '') === 'list' ? 'is-active' : '' }}"
                    data-view="list"
                    aria-label="List view"
                    aria-pressed="{{ ($filters['view'] ?? '') === 'list' ? 'true' : 'false' }}"
                >
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
</div>
