@extends('frontend.layouts.app')

@section('title', 'Kundan Collection | Geetanjali Jewellers')
@section('meta_description', "Explore Geetanjali Jewellers' handcrafted Kundan jewellery collection featuring necklaces, earrings, bangles, rings, bridal sets and more.")

@push('styles')
    <link rel="canonical" href="{{ url('/kundan') }}">
@endpush

@section('content')
    <div class="kundan-page" data-kundan-page>
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        @include('frontend.components.collection.hero', ['hero' => $hero])

        @include('frontend.components.collection.category-nav', ['categories' => $categoryNav])

        @include('frontend.components.collection.intro')

        <section class="kundan-listing" id="collection-products" aria-label="Kundan products">
            <div class="site-container">
                <div class="kundan-listing__toolbar d-lg-none">
                    <button type="button" class="kundan-filter-toggle" data-filter-open aria-controls="kundanFilterDrawer" aria-expanded="false">
                        <i class="bi bi-sliders" aria-hidden="true"></i>
                        Filter
                    </button>
                    @include('frontend.components.collection.view-toggle', ['view' => $filters['view'] ?? 'grid'])
                    <form method="get" action="{{ route('collections.kundan') }}" class="kundan-sort-mobile">
                        @foreach (($filters['type'] ?? []) as $t)
                            <input type="hidden" name="type[]" value="{{ $t }}">
                        @endforeach
                        @foreach (($filters['metal'] ?? []) as $m)
                            <input type="hidden" name="metal[]" value="{{ $m }}">
                        @endforeach
                        @foreach (($filters['stone'] ?? []) as $s)
                            <input type="hidden" name="stone[]" value="{{ $s }}">
                        @endforeach
                        <input type="hidden" name="min_price" value="{{ $filters['min_price'] }}">
                        <input type="hidden" name="max_price" value="{{ $filters['max_price'] }}">
                        @if (!empty($filters['category']))
                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                        @endif
                        <input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}">
                        <label class="visually-hidden" for="kundan-sort-mobile">Sort by</label>
                        <select id="kundan-sort-mobile" name="sort" class="kundan-sort-select" data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()">
                            <option value="popularity" @selected($filters['sort'] === 'popularity')>Popularity</option>
                            <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                            <option value="price_low" @selected($filters['sort'] === 'price_low')>Price Low to High</option>
                            <option value="price_high" @selected($filters['sort'] === 'price_high')>Price High to Low</option>
                            <option value="rating" @selected($filters['sort'] === 'rating')>Best Rated</option>
                        </select>
                    </form>
                </div>

                <div class="kundan-listing__layout">
                    <aside class="kundan-sidebar d-none d-lg-block" aria-label="Product filters">
                        @include('frontend.components.collection.filter-sidebar', [
                            'filters' => $filters,
                            'filterCounts' => $filterCounts,
                            'minPriceBound' => $minPriceBound,
                            'maxPriceBound' => $maxPriceBound,
                            'formId' => 'kundan-filter-form',
                        ])
                    </aside>

                    <div class="kundan-products">
                        <div class="kundan-products__header">
                            <p class="kundan-products__count">
                                @if ($products->total() > 0)
                                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
                                @else
                                    Showing 0 products
                                @endif
                            </p>

                            <div class="kundan-products__tools">
                                <form method="get" action="{{ route('collections.kundan') }}" class="kundan-sort d-none d-lg-flex">
                                    @foreach (($filters['type'] ?? []) as $t)
                                        <input type="hidden" name="type[]" value="{{ $t }}">
                                    @endforeach
                                    @foreach (($filters['metal'] ?? []) as $m)
                                        <input type="hidden" name="metal[]" value="{{ $m }}">
                                    @endforeach
                                    @foreach (($filters['stone'] ?? []) as $s)
                                        <input type="hidden" name="stone[]" value="{{ $s }}">
                                    @endforeach
                                    <input type="hidden" name="min_price" value="{{ $filters['min_price'] }}">
                                    <input type="hidden" name="max_price" value="{{ $filters['max_price'] }}">
                                    @if (!empty($filters['category']))
                                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                    @endif
                                    <input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}">
                                    <label for="kundan-sort">Sort by:</label>
                                    <select id="kundan-sort" name="sort" class="kundan-sort-select" data-auto-submit onchange="this.form.dataset.listingSubmitting='1'; this.form.submit()">
                                        <option value="popularity" @selected($filters['sort'] === 'popularity')>Popularity</option>
                                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                                        <option value="price_low" @selected($filters['sort'] === 'price_low')>Price Low to High</option>
                                        <option value="price_high" @selected($filters['sort'] === 'price_high')>Price High to Low</option>
                                        <option value="rating" @selected($filters['sort'] === 'rating')>Best Rated</option>
                                    </select>
                                </form>
                                <div class="d-none d-lg-flex">
                                    @include('frontend.components.collection.view-toggle', ['view' => $filters['view'] ?? 'grid'])
                                </div>
                            </div>
                        </div>

                        @if ($products->count() > 0)
                            <div class="kundan-grid{{ ($filters['view'] ?? 'grid') === 'list' ? ' kundan-grid--list' : '' }}">
                                @foreach ($products as $product)
                                    @include('frontend.components.product-card', ['product' => $product])
                                @endforeach
                            </div>

                            <div class="kundan-pagination">
                                {{ $products->fragment('collection-products')->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="kundan-empty">
                                <p>No products match your filters.</p>
                                <a href="{{ route('collections.kundan') }}" class="btn-apply-filters">Clear filters</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @include('frontend.components.collection.service-strip', ['services' => $services])

        @include('frontend.components.collection.why-kundan', ['features' => $whyFeatures])
    </div>

    {{-- Mobile filter drawer --}}
    <div class="kundan-filter-drawer" id="kundanFilterDrawer" data-filter-drawer hidden>
        <div class="kundan-filter-drawer__backdrop" data-filter-close tabindex="-1"></div>
        <div class="kundan-filter-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="kundan-filter-drawer-title">
            <div class="kundan-filter-drawer__head">
                <h2 id="kundan-filter-drawer-title">Filters</h2>
                <button type="button" class="kundan-filter-drawer__close" data-filter-close aria-label="Close filters">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="kundan-filter-drawer__body">
                @include('frontend.components.collection.filter-sidebar', [
                    'filters' => $filters,
                    'filterCounts' => $filterCounts,
                    'minPriceBound' => $minPriceBound,
                    'maxPriceBound' => $maxPriceBound,
                    'formId' => 'kundan-filter-form-mobile',
                ])
            </div>
        </div>
    </div>
@endsection
