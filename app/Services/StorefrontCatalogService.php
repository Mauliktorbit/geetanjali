<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Collection as StorefrontCollection;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class StorefrontCatalogService
{
    public const PER_PAGE = 12;

    public const MIN_PRICE = 2000;

    public const MAX_PRICE = 500000;

    public const TYPE_KEYS = ['necklaces', 'earrings', 'rings', 'bangles', 'maang-tikka', 'bracelets', 'sets', 'jhumkas'];

    public const METAL_KEYS = ['gold-plated', 'oxidized', 'silver-plated', 'antique'];

    public const METAL_LABELS = [
        'gold-plated' => 'Gold-plated',
        'oxidized' => 'Oxidized',
        'silver-plated' => 'Silver-plated',
        'antique' => 'Antique',
    ];

    public const STONE_KEYS = ['emerald', 'ruby', 'pearl', 'polki', 'meenakari'];

    public const PAGE_SLUGS = ['kundan', 'bridal', 'new-arrivals'];

    public const PAGE_COLLECTIONS = [
        'kundan' => 'Kundan Collection',
        'bridal' => 'Bridal Collection',
        'new-arrivals' => 'New Arrivals',
    ];

    /**
     * Active jewellery types from Admin → Categories.
     *
     * @return Collection<int, Category|object{id: int|null, name: string, slug: string, image: string|null}>
     */
    public static function jewelleryTypes(): Collection
    {
        $types = Category::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image'])
            ->filter(fn ($type) => ! self::isPlaceholderCategory($type->name, $type->slug))
            ->values();

        if ($types->isNotEmpty()) {
            return $types;
        }

        return collect(self::TYPE_KEYS)->map(fn (string $slug) => (object) [
            'id' => null,
            'name' => Str::title(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'image' => null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function jewelleryTypeOptions(): array
    {
        return self::jewelleryTypes()->mapWithKeys(fn ($type) => [$type->slug => $type->name])->all();
    }

    /**
     * @return list<string>
     */
    public static function jewelleryTypeSlugs(): array
    {
        return self::jewelleryTypes()->pluck('slug')->filter()->values()->all();
    }

    public static function categoryImage(?string $slug, ?string $image = null): string
    {
        if (filled($image)) {
            return $image;
        }

        $defaults = [
            'necklaces' => 'public/assets/images/categories/necklaces.jpg',
            'earrings' => 'public/assets/images/categories/earrings.jpg',
            'rings' => 'public/assets/images/categories/rings.jpg',
            'bangles' => 'public/assets/images/categories/bangles.jpg',
            'maang-tikka' => 'public/assets/images/categories/mangalsutra.jpg',
            'bracelets' => 'public/assets/images/categories/bangles.jpg',
            'sets' => 'public/assets/images/categories/bridal.jpg',
            'jhumkas' => 'public/assets/images/categories/earrings.jpg',
            'mangalsutra' => 'public/assets/images/categories/mangalsutra.jpg',
            'bridal' => 'public/assets/images/categories/bridal.jpg',
            'kundan' => 'public/assets/images/categories/kundan.jpg',
            'diamond' => 'public/assets/images/categories/diamond.jpg',
        ];

        return $defaults[$slug] ?? 'public/assets/images/categories/kundan.jpg';
    }

    public static function isActiveSlug(string $slug): bool
    {
        return StorefrontCollection::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }

    public static function isProtectedSlug(?string $slug): bool
    {
        return in_array((string) $slug, self::PAGE_SLUGS, true);
    }

    public static function storefrontUrl(StorefrontCollection $collection): string
    {
        return match ($collection->slug) {
            'kundan' => url('/kundan'),
            'bridal' => url('/bridal-collection'),
            'new-arrivals' => url('/new-arrivals'),
            default => url('/collection/'.$collection->slug),
        };
    }

    public static function ensurePageCollections(): Collection
    {
        self::seedPageCollections();

        return self::adminCollections();
    }

    /**
     * All collections for admin product assignment and filters.
     *
     * @return Collection<int, StorefrontCollection>
     */
    public static function adminCollections(): Collection
    {
        self::seedPageCollections();

        return StorefrontCollection::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_active']);
    }

    /**
     * Collections for the storefront menu. Every admin collection appears here.
     *
     * @return Collection<int, StorefrontCollection>
     */
    public static function navCollections(): Collection
    {
        return once(function () {
            self::seedPageCollections();

            return StorefrontCollection::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });
    }

    /**
     * @return list<array{label: string, route?: string|null, href?: string, dropdown?: list<array{label: string, url: string}>}>
     */
    public static function navMenuItems(): array
    {
        $items = [
            ['label' => 'Home', 'route' => 'home'],
        ];

        $extra = [];

        foreach (self::navCollections() as $collection) {
            $entry = [
                'label' => $collection->name,
                'href' => self::storefrontUrl($collection),
                'route' => match ($collection->slug) {
                    'kundan' => 'collections.kundan',
                    'bridal' => 'collections.bridal',
                    'new-arrivals' => 'products.new-arrivals',
                    default => null,
                },
            ];

            if (self::isProtectedSlug($collection->slug)) {
                $items[] = $entry;
                continue;
            }

            $extra[] = [
                'label' => $collection->name,
                'url' => $entry['href'],
            ];
        }

        if ($extra !== []) {
            $attached = false;
            foreach ($items as $index => $item) {
                if (($item['route'] ?? null) !== 'products.new-arrivals') {
                    continue;
                }

                $items[$index]['label'] = 'New Collection';
                $items[$index]['dropdown'] = array_merge([
                    ['label' => 'New Arrivals', 'url' => $item['href']],
                ], $extra);
                $attached = true;
                break;
            }

            if (! $attached) {
                $items[] = [
                    'label' => 'New Collection',
                    'route' => 'products.new-arrivals',
                    'href' => url('/new-arrivals'),
                    'dropdown' => array_merge([
                        ['label' => 'New Arrivals', 'url' => url('/new-arrivals')],
                    ], $extra),
                ];
            }
        }

        return array_merge($items, [
            ['label' => 'Offers', 'route' => 'offers.index'],
            ['label' => 'About Us', 'route' => 'about'],
            ['label' => 'Contact Us', 'route' => 'contact'],
        ]);
    }

    private static function seedPageCollections(): void
    {
        foreach (self::PAGE_COLLECTIONS as $slug => $name) {
            StorefrontCollection::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'type' => $slug,
                    'description' => $name,
                    'is_active' => true,
                    'sort_order' => array_search($slug, self::PAGE_SLUGS, true) + 1,
                ]
            );
        }
    }

    /**
     * @return array{
     *     type: list<string>,
     *     metal: list<string>,
     *     stone: list<string>,
     *     min_price: int,
     *     max_price: int,
     *     sort: string,
     *     category: string|null
     * }
     */
    public function normalizeFilters(Request $request, int $minBound = self::MIN_PRICE, int $maxBound = self::MAX_PRICE): array
    {
        $type = array_values(array_filter((array) $request->input('type', [])));
        $metal = array_values(array_filter((array) $request->input('metal', [])));
        $stone = array_values(array_filter((array) $request->input('stone', [])));

        if ($request->filled('category') && $type === []) {
            $type = [(string) $request->input('category')];
        }

        $min = (int) $request->input('min_price', $minBound);
        $max = (int) $request->input('max_price', $maxBound);

        $min = max($minBound, min($min, $maxBound));
        $max = max($minBound, min($max, $maxBound));

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        $sort = (string) $request->input('sort', 'popularity');
        $allowedSorts = ['popularity', 'newest', 'price_low', 'price_high', 'rating'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'popularity';
        }

        $view = (string) $request->input('view', 'grid');
        if (! in_array($view, ['grid', 'list'], true)) {
            $view = 'grid';
        }

        return [
            'type' => $type,
            'metal' => $metal,
            'stone' => $stone,
            'min_price' => $min,
            'max_price' => $max,
            'sort' => $sort,
            'category' => $request->filled('category') ? (string) $request->input('category') : null,
            'view' => $view,
        ];
    }

    /**
     * Live product cards for homepage collection tabs.
     *
     * @return list<array<string, mixed>>
     */
    public function homeCards(string $slug, int $limit = 4): array
    {
        return Product::query()
            ->inCollection($slug)
            ->with(['category', 'inventories'])
            ->orderByDesc('is_bestseller')
            ->orderByDesc('sold_count')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => $this->toCard($product))
            ->values()
            ->all();
    }

    public function paginateKundan(array $filters): LengthAwarePaginator
    {
        return $this->paginateCollection('kundan', $filters);
    }

    public function paginateCollection(string $slug, array $filters): LengthAwarePaginator
    {
        $query = Product::query()->inCollection($slug)->with(['category', 'inventories']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? 'popularity');

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    public function paginateSearch(string $term): LengthAwarePaginator
    {
        $query = Product::query()->storefront()->with(['category', 'inventories']);
        $this->applySearch($query, $term);
        $this->applySort($query, 'popularity');

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    public function applySearch(Builder $query, string $term): void
    {
        $term = trim(preg_replace('/\s+/', ' ', $term) ?? '');
        if ($term === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('short_description', 'like', $like)
                ->orWhere('metal', 'like', $like)
                ->orWhere('stone', 'like', $like)
                ->orWhere('style', 'like', $like)
                ->orWhere('occasion', 'like', $like)
                ->orWhereHas('category', function (Builder $category) use ($like) {
                    $category->where('name', 'like', $like)->orWhere('slug', 'like', $like);
                })
                ->orWhereHas('collections', function (Builder $collection) use ($like) {
                    $collection->where('name', 'like', $like)->orWhere('slug', 'like', $like);
                });
        });
    }

    /**
     * @return array{type: array<string, int>, metal: array<string, int>, stone: array<string, int>, total: int}
     */
    public function filterCounts(?string $collectionSlug = 'kundan'): array
    {
        $catalog = Product::query()->inCollection($collectionSlug)->with('category')->get();

        $type = [];
        foreach (self::jewelleryTypeSlugs() as $key) {
            $type[$key] = 0;
        }

        foreach ($catalog as $product) {
            $slug = (string) ($product->category?->slug ?? '');
            if ($slug === '' && str_contains(strtolower((string) $product->name), 'set')) {
                $slug = 'sets';
            }
            if ($slug === '') {
                continue;
            }
            if (! array_key_exists($slug, $type)) {
                $type[$slug] = 0;
            }
            $type[$slug]++;
        }

        $metal = [];
        foreach (self::METAL_KEYS as $key) {
            $metal[$key] = 0;
        }
        foreach ($catalog as $product) {
            $key = $this->metalKey($product);
            if ($key && array_key_exists($key, $metal)) {
                $metal[$key]++;
            }
        }

        $stone = [];
        foreach (array_merge(self::STONE_KEYS, ['kundan', 'diamond']) as $key) {
            $stone[$key] = 0;
        }
        foreach ($catalog as $product) {
            foreach (array_keys($stone) as $key) {
                if ($this->hasStone($product, $key)) {
                    $stone[$key]++;
                    break;
                }
            }
        }

        return [
            'type' => $type,
            'metal' => $metal,
            'stone' => $stone,
            'total' => $catalog->count(),
        ];
    }

    /**
     * @return array{min: int, max: int}
     */
    public function priceBounds(?string $collectionSlug = 'kundan'): array
    {
        $prices = Product::query()
            ->inCollection($collectionSlug)
            ->get(['regular_price', 'sale_price'])
            ->map(fn (Product $p) => (int) $p->effective_price)
            ->filter(fn (int $price) => $price > 0);

        if ($prices->isEmpty()) {
            return ['min' => self::MIN_PRICE, 'max' => self::MAX_PRICE];
        }

        $min = max(0, (int) floor($prices->min() / 1000) * 1000);
        $max = (int) ceil($prices->max() / 1000) * 1000;

        if ($min >= $max) {
            $max = $min + 10000;
        }

        return ['min' => max(0, $min), 'max' => $max];
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return Product::query()
            ->storefront()
            ->with([
                'category',
                'inventories',
                'approvedReviews',
                'relatedProducts' => fn ($q) => $q->storefront()->with(['inventories', 'category']),
                'collections',
            ])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function relatedCards(Product $product, int $limit = 8): Collection
    {
        $exclude = [(int) $product->id];
        $related = $product->relatedProducts
            ->filter(fn (Product $item) => $item->is_active && ! $item->is_archived && $this->isSameStorefrontType($product, $item))
            ->values();

        $exclude = array_values(array_unique(array_merge($exclude, $related->pluck('id')->map(fn ($id) => (int) $id)->all())));

        if ($related->count() < $limit) {
            $extra = $this->sameTypeQuery($product)
                ->whereKeyNot($exclude)
                ->with(['inventories', 'category'])
                ->latest()
                ->limit($limit - $related->count())
                ->get();
            $related = $related->concat($extra);
            $exclude = array_values(array_unique(array_merge($exclude, $related->pluck('id')->map(fn ($id) => (int) $id)->all())));
        }

        if ($related->count() < $limit) {
            $collectionIds = $product->collections->pluck('id')->filter()->values();
            if ($collectionIds->isNotEmpty()) {
                $extra = $this->sameTypeQuery($product)
                    ->whereKeyNot($exclude)
                    ->whereHas('collections', fn ($q) => $q->whereIn('collections.id', $collectionIds))
                    ->with(['inventories', 'category'])
                    ->latest()
                    ->limit($limit - $related->count())
                    ->get();
                $related = $related->concat($extra);
            }
        }

        return $related->take($limit)->map(fn (Product $item) => $this->toCard($item))->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rememberAndRecentlyViewed(Product $product, int $limit = 8): Collection
    {
        $currentId = (int) $product->id;
        $ids = collect(Session::get('recently_viewed_product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $currentId)
            ->prepend($currentId)
            ->unique()
            ->take(16)
            ->values()
            ->all();

        Session::put('recently_viewed_product_ids', $ids);

        $others = array_values(array_filter($ids, fn ($id) => $id !== $currentId));
        if ($others === []) {
            return collect();
        }

        $order = array_flip(array_slice($others, 0, $limit));

        return Product::query()
            ->storefront()
            ->whereIn('id', array_keys($order))
            ->with(['inventories', 'category'])
            ->get()
            ->sortBy(fn (Product $item) => $order[(int) $item->id] ?? 999)
            ->values()
            ->map(fn (Product $item) => $this->toCard($item));
    }

    /**
     * @param  list<int>  $excludeIds
     * @return Collection<int, array<string, mixed>>
     */
    public function recentlyViewedCards(array $excludeIds = [], int $limit = 8): Collection
    {
        $excludeIds = array_values(array_filter(array_map('intval', $excludeIds)));
        $ids = collect(Session::get('recently_viewed_product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && ! in_array($id, $excludeIds, true))
            ->unique()
            ->take($limit)
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        $order = array_flip($ids);

        return Product::query()
            ->storefront()
            ->whereIn('id', $ids)
            ->with(['inventories', 'category'])
            ->get()
            ->sortBy(fn (Product $item) => $order[(int) $item->id] ?? 999)
            ->values()
            ->map(fn (Product $item) => $this->toCard($item));
    }

    /**
     * @param  list<int>  $excludeIds
     * @return Collection<int, array<string, mixed>>
     */
    public function trendingCards(array $excludeIds = [], int $limit = 8, ?Product $similarTo = null): Collection
    {
        $excludeIds = array_values(array_filter(array_map('intval', $excludeIds)));

        $query = Product::query()
            ->storefront()
            ->when($excludeIds !== [], fn ($q) => $q->whereKeyNot($excludeIds))
            ->with(['inventories', 'category']);

        if ($similarTo) {
            $this->applySameTypeConstraint($query, $similarTo);
        }

        return $query
            ->orderByDesc('is_bestseller')
            ->orderByDesc('sold_count')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn (Product $item) => $this->toCard($item))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function toCard(Product $product): array
    {
        $price = (float) $product->effective_price;
        $compare = (float) $product->regular_price;
        $discount = price_discount_label($price, $compare);
        $stock = $this->availableStock($product);
        $images = $this->gallery($product);

        return [
            'id' => (int) $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'type' => $this->typeKey($product),
            'metal_key' => $this->metalKey($product),
            'metal' => $this->storefrontMetalLabel($product),
            'stones' => $this->stoneKeys($product),
            'price' => $price,
            'compare_at_price' => $compare > $price ? $compare : null,
            'discount_label' => $discount,
            'badge' => $this->badge($product),
            'is_featured' => (bool) $product->is_featured,
            'is_bestseller' => (bool) $product->is_bestseller,
            'rating' => (float) ($product->avg_rating ?: 0),
            'review_count' => (int) $product->review_count,
            'sold' => (int) $product->sold_count,
            'image' => $images[0]->url ?? self::categoryImage($this->typeKey($product)),
            'weight' => $this->weightLabel($product),
            'url' => route('products.show', $product->slug),
            'similar_url' => $this->typeKey($product)
                ? route('collections.kundan', ['category' => $this->typeKey($product)])
                : route('products.new-arrivals'),
            'stock' => $stock,
            'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
        ];
    }

    public function toDetail(Product $product): object
    {
        $images = $this->gallery($product);
        $reviews = $product->approvedReviews->map(fn ($review) => (object) [
            'name' => $review->customer_name ?: 'Customer',
            'rating' => (int) $review->rating,
            'date' => optional($review->created_at)->format('d M Y'),
            'text' => $review->comment,
        ]);

        $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($product->approvedReviews as $review) {
            $star = max(1, min(5, (int) $review->rating));
            $breakdown[$star]++;
        }

        $stock = (int) $product->inventories->sum('available_stock');
        $price = (float) $product->regular_price;
        $sale = $product->sale_price !== null ? (float) $product->sale_price : null;
        $sold = (int) $product->sold_count;
        [$qtyMin, $qtyMax] = $this->quantityBounds($product, $stock);

        return (object) [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'sku' => $product->sku,
            'type' => $this->typeKey($product),
            'category' => $this->storefrontCategoryName($product),
            'badge' => $this->badge($product),
            'price' => $price,
            'sale_price' => $sale && $sale > 0 && $sale < $price ? $sale : null,
            'discount_label' => price_discount_label($sale && $sale > 0 && $sale < $price ? $sale : $price, $price),
            'tax_note' => $product->tax_note ?: 'Inclusive of all taxes',
            'short_description' => $this->storefrontProse($product->short_description),
            'description' => $this->storefrontProse($product->description),
            'metal' => $this->storefrontMetalLabel($product),
            'purity' => $this->storefrontMetalLabel($product),
            'stone' => $this->storefrontProse($product->stone),
            'style' => $this->storefrontProse($product->style),
            'weight' => $this->weightLabel($product),
            'dimensions' => $this->dimensionsLabel($product),
            'occasion' => $this->storefrontProse($product->occasion),
            'certification' => $this->qualityLabel($product),
            'stock' => $stock,
            'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
            'qty_min' => $qtyMin,
            'qty_max' => $qtyMax,
            'similar_url' => $this->typeKey($product)
                ? route('collections.kundan', ['category' => $this->typeKey($product)])
                : route('products.new-arrivals'),
            'rating' => $reviews->count() > 0
                ? round((float) $reviews->avg('rating'), 1)
                : (float) ($product->avg_rating ?: 0),
            'review_count' => $reviews->count() > 0 ? $reviews->count() : (int) $product->review_count,
            'sold_count' => $sold > 0 ? (string) $sold : '0',
            'image' => $images[0]->url ?? self::categoryImage($this->typeKey($product)),
            'images' => $images,
            'highlights' => $this->storefrontHighlights($product),
            'benefits' => $this->benefits($product),
            'about_points' => $this->aboutPoints($product),
            'reviews' => $reviews,
            'rating_breakdown' => $breakdown,
            'estimated_delivery' => $product->estimated_delivery ?: '3–5 business days',
            'care_instructions' => $this->storefrontCareLines($product),
            'shipping_information' => array_map(fn (string $line) => $this->storefrontProse($line), $this->textLines($product->shipping_information)),
            'return_policy' => array_map(fn (string $line) => $this->storefrontProse($line), $this->textLines($product->return_policy)),
        ];
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['type'])) {
            $slugs = [];
            foreach ($filters['type'] as $type) {
                $key = strtolower(trim((string) $type));
                if ($key === '') {
                    continue;
                }
                if ($key === 'bridal-sets') {
                    $slugs[] = 'sets';
                    $slugs[] = 'bridal';
                    continue;
                }
                $slugs[] = $key;
            }
            $slugs = array_values(array_unique($slugs));

            if ($slugs !== []) {
                $includeUncategorizedSets = count(array_intersect($slugs, ['sets', 'bridal'])) > 0;
                $query->where(function (Builder $q) use ($slugs, $includeUncategorizedSets) {
                    $q->whereHas('category', fn (Builder $category) => $category->whereIn('slug', $slugs));
                    if ($includeUncategorizedSets) {
                        $q->orWhere(function (Builder $inner) {
                            $inner->whereNull('category_id')
                                ->where('name', 'like', '%set%');
                        });
                    }
                });
            }
        }

        if (! empty($filters['metal'])) {
            $query->where(function (Builder $q) use ($filters) {
                foreach ($filters['metal'] as $metal) {
                    foreach ($this->metalSearchTerms((string) $metal) as $term) {
                        $q->orWhere('purity', 'like', '%'.$term.'%')
                            ->orWhere('metal', 'like', '%'.$term.'%');
                    }
                }
            });
        }

        if (! empty($filters['stone'])) {
            $query->where(function (Builder $q) use ($filters) {
                foreach ($filters['stone'] as $stone) {
                    $q->orWhere('stone', 'like', '%'.$stone.'%');
                }
            });
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null && $filters['min_price'] !== '') {
            $query->whereRaw('COALESCE(sale_price, regular_price) >= ?', [$filters['min_price']]);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== null && $filters['max_price'] !== '') {
            $query->whereRaw('COALESCE(sale_price, regular_price) <= ?', [$filters['max_price']]);
        }

        if (! empty($filters['occasion'])) {
            $occasions = (array) $filters['occasion'];
            $query->where(function (Builder $q) use ($occasions) {
                foreach ($occasions as $occasion) {
                    $q->orWhere('occasion', 'like', '%'.$occasion.'%');
                }
            });
        }
    }

    public function applySort(Builder $query, string $sort): void
    {
        $query->orderByRaw($this->outOfStockSortSql().' asc');

        match ($sort) {
            'price_low' => $query->orderByRaw('COALESCE(sale_price, regular_price) asc'),
            'price_high' => $query->orderByRaw('COALESCE(sale_price, regular_price) desc'),
            'newest' => $query->latest(),
            'rating' => $query->orderByDesc('avg_rating')->orderByDesc('review_count'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'bestselling' => $query->orderByDesc('sold_count')->orderByDesc('is_bestseller'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('is_bestseller')->orderByDesc('sold_count'),
            default => $query->orderByDesc('is_bestseller')->orderByDesc('sold_count')->orderByDesc('view_count'),
        };
    }

    private function outOfStockSortSql(): string
    {
        return 'CASE WHEN (SELECT COALESCE(SUM(available_stock), 0) FROM inventories WHERE inventories.product_id = products.id) > 0 THEN 0 ELSE 1 END';
    }

    /**
     * @return list<object{url: string, alt: string}>
     */
    private function gallery(Product $product): array
    {
        $paths = [];
        if ($product->main_image) {
            $paths[] = $product->main_image;
        }
        foreach ((array) $product->gallery_images as $image) {
            if ($image && ! in_array($image, $paths, true)) {
                $paths[] = $image;
            }
        }

        if ($paths === []) {
            $paths[] = self::categoryImage($product->category?->slug);
        } else {
            $paths = array_values(array_unique(array_map(
                fn (string $path) => $this->storefrontImagePath($product, $path),
                $paths
            )));
        }

        return array_map(fn (string $path) => (object) [
            'url' => $path,
            'alt' => $product->name,
        ], $paths);
    }

    private function availableStock(Product $product): int
    {
        if ($product->relationLoaded('inventories')) {
            return (int) $product->inventories->sum('available_stock');
        }

        return (int) $product->inventories()->sum('available_stock');
    }

    private function badge(Product $product): ?string
    {
        if (filled($product->badge)) {
            return $product->badge;
        }

        if ($product->is_bestseller) {
            return 'BESTSELLER';
        }

        if ($product->is_new_arrival) {
            return 'NEW';
        }

        return null;
    }

    private function weightLabel(Product $product): ?string
    {
        if ($product->weight === null) {
            return null;
        }

        $formatted = rtrim(rtrim(number_format((float) $product->weight, 3, '.', ''), '0'), '.');

        return $formatted.' g (Approx)';
    }

    private function dimensionsLabel(Product $product): ?string
    {
        if (filled($product->dimensions_text)) {
            return $product->dimensions_text;
        }

        $parts = array_filter([
            $product->length ? 'L '.$product->length : null,
            $product->width ? 'W '.$product->width : null,
            $product->height ? 'H '.$product->height : null,
        ]);

        return $parts !== [] ? implode(' × ', $parts).' cm' : null;
    }

    /**
     * @return list<array{icon: string, label: string}>
     */
    private function benefits(Product $product): array
    {
        return [
            ['icon' => 'bi-circle', 'label' => $this->storefrontMetalLabel($product)],
            ['icon' => 'bi-award', 'label' => $this->qualityLabel($product)],
            ['icon' => 'bi-arrow-left-right', 'label' => 'Easy 15-day returns'],
            ['icon' => 'bi-patch-check', 'label' => 'Premium anti-tarnish finish'],
        ];
    }

    private function storefrontHighlights(Product $product): array
    {
        $items = [];
        $type = $this->typeKey($product);
        $isBridal = $type === 'sets' || $type === 'bridal' || str_contains(strtolower($product->name), 'bridal');

        foreach ((array) ($product->highlights ?? []) as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }

            if (preg_match('/hallmark|\bbis\b|22k|18k|24k/i', $text)) {
                $text = 'Handcrafted fashion jewellery with a premium anti-tarnish finish';
            } elseif (preg_match('/authenticity certificate/i', $text)) {
                $text = 'Each piece is quality-checked before dispatch';
            } elseif (preg_match('/lifetime service/i', $text)) {
                $text = 'After-sales care available';
            } elseif (preg_match('/bridal and festive/i', $text) && ! $isBridal) {
                $text = 'Designed for festive occasions and special evenings';
            } else {
                $text = $this->storefrontProse($text);
            }

            $items[] = $text;
        }

        return array_values(array_unique($items));
    }

    /**
     * @return list<array{icon: string, label: string}>
     */
    private function aboutPoints(Product $product): array
    {
        $category = $this->storefrontCategoryName($product);
        $metal = $this->storefrontMetalLabel($product);
        $stone = trim($this->storefrontProse($product->stone));
        $occasion = trim($this->storefrontProse($product->occasion));
        $type = $this->typeKey($product);

        $points = [];
        if ($category !== '') {
            $points[] = [
                'icon' => 'bi-gem',
                'label' => $category.' from the Geetanjali fashion jewellery collection',
            ];
        }
        if ($metal !== '') {
            $points[] = [
                'icon' => 'bi-circle',
                'label' => $metal.' with a premium anti-tarnish finish',
            ];
        }
        if ($stone !== '') {
            $points[] = [
                'icon' => 'bi-stars',
                'label' => 'Set with '.$stone,
            ];
        }
        if ($occasion !== '') {
            $points[] = [
                'icon' => 'bi-calendar-event',
                'label' => 'Styled for '.$occasion,
            ];
        } elseif (in_array($type, ['sets', 'bridal'], true) || str_contains(strtolower($product->name), 'bridal')) {
            $points[] = [
                'icon' => 'bi-calendar-event',
                'label' => 'Made for weddings, receptions and festive wear',
            ];
        } else {
            $points[] = [
                'icon' => 'bi-calendar-event',
                'label' => 'Made for festive occasions and special evenings',
            ];
        }
        $points[] = [
            'icon' => 'bi-patch-check',
            'label' => 'Quality-checked before dispatch',
        ];

        return array_slice($points, 0, 4);
    }

    /**
     * @return list<string>
     */
    private function storefrontCareLines(Product $product): array
    {
        $defaults = [
            'Keep away from perfumes, sprays and household chemicals',
            'Store in a dry pouch, away from other jewellery',
            'Avoid water, sweat and prolonged moisture',
            'Wipe gently with a soft dry cloth after wearing',
            'Do not use jewellery cleaning dips or ultrasonic cleaners',
        ];

        $lines = array_values(array_filter(
            array_map(fn (string $line) => $this->storefrontProse($line), $this->textLines($product->care_instructions)),
            fn (string $line) => $line !== '' && ! preg_match('/professional cleaning|ultrasonic|hallmark|22k|18k|24k|\bbis\b/i', $line)
        ));

        return $lines !== [] ? $lines : $defaults;
    }

    private function qualityLabel(Product $product): string
    {
        $value = trim((string) ($product->certification ?? ''));

        if ($value === '' || preg_match('/hallmark|\bbis\b/i', $value)) {
            return 'Quality-checked finish';
        }

        return $this->storefrontProse($value);
    }

    private function storefrontMetalLabel(Product $product): string
    {
        $raw = trim((string) ($product->metal ?? ''));
        $key = $this->metalKey($product);

        if ($raw !== '' && ! preg_match('/\b(22k|18k|24k|hallmark|\bbis\b|yellow gold)\b/i', $raw) && ! preg_match('/^gold$/i', $raw)) {
            return $raw;
        }

        if ($key && isset(self::METAL_LABELS[$key])) {
            return self::METAL_LABELS[$key];
        }

        return $raw !== '' ? $raw : 'Fashion jewellery';
    }

    private function storefrontProse(?string $text): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $pairs = [
            '/\bBIS[\s-]*Hallmarked\b/i' => 'quality-checked',
            '/\bHallmarked Gold\b/i' => 'premium-finish plating',
            '/\bHallmarked\b/i' => 'premium-finish',
            '/\b(?:22|18|24)\s*K(?:T)?\s+Pure Gold\b/i' => 'gold-plated metal',
            '/\b(?:22|18|24)\s*K(?:T)?\s+Yellow Gold\b/i' => 'gold-plated metal',
            '/\b(?:22|18|24)\s*K(?:T)?\s+[Gg]old\b/i' => 'gold-plated metal',
            '/\bYellow Gold\b/i' => 'gold-plated metal',
            '/\bcertified Kundan stones\b/i' => 'hand-set kundan stones',
            '/\bcertified Kundan\b/i' => 'hand-set kundan',
            '/authenticity certificate/i' => 'quality check',
        ];

        foreach ($pairs as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function quantityBounds(Product $product, int $stock): array
    {
        $min = max(1, (int) ($product->min_order_qty ?: 1));
        $max = max(0, $stock);
        $maxOrder = (int) ($product->max_order_qty ?: 0);
        if ($maxOrder > 0) {
            $max = min($max, $maxOrder);
        }
        $max = min($max, CartService::MAX_QUANTITY);

        if ($max < 1) {
            return [1, 1];
        }

        if ($min > $max) {
            $min = $max;
        }

        return [$min, $max];
    }

    private function storefrontCategoryName(Product $product): string
    {
        $name = trim((string) ($product->category?->name ?? ''));
        $slug = (string) ($product->category?->slug ?? '');

        if (self::isPlaceholderCategory($name, $slug)) {
            return $this->categoryNameFromProductName($product->name) ?: 'Jewellery';
        }

        return $name;
    }

    private function categoryNameFromProductName(string $name): ?string
    {
        $slug = $this->typeSlugFromName($name);
        if ($slug === null) {
            return null;
        }

        return match ($slug) {
            'maang-tikka' => 'Maang Tikka',
            default => Str::title(str_replace('-', ' ', $slug)),
        };
    }

    private function storefrontImagePath(Product $product, string $path): string
    {
        $path = trim($path);
        $type = $this->typeKey($product);

        if ($path === '') {
            return self::categoryImage($type);
        }

        if (str_starts_with($path, 'uploads/')) {
            return $path;
        }

        if ($this->demoImageMismatchesCategory($path, $type, $product->name)) {
            return self::categoryImage($type);
        }

        return $path;
    }

    private function demoImageMismatchesCategory(string $path, ?string $slug, ?string $name = null): bool
    {
        $path = strtolower($path);
        $slug = strtolower((string) $slug);
        $name = strtolower((string) $name);

        if ($slug === '' || self::isPlaceholderCategory($slug, $slug)) {
            $slug = $this->typeSlugFromName($name) ?: $slug;
        }

        $hints = [
            'necklaces' => ['necklace', 'choker', 'pendant'],
            'earrings' => ['earring', 'jhumka', 'drop'],
            'jhumkas' => ['earring', 'jhumka', 'drop'],
            'rings' => ['ring'],
            'bangles' => ['bangle'],
            'bracelets' => ['bracelet', 'bangle'],
            'maang-tikka' => ['tikka', 'mangalsutra'],
            'sets' => ['bridal', 'set', 'necklace'],
            'bridal' => ['bridal', 'set', 'necklace'],
        ];
        $conflicts = [
            'necklaces' => ['earring', 'jhumka', 'ring', 'bangle', 'bracelet', 'tikka'],
            'earrings' => ['necklace', 'choker', 'pendant', 'bangle', 'bracelet', 'ring', 'tikka'],
            'jhumkas' => ['necklace', 'choker', 'pendant', 'bangle', 'bracelet', 'ring', 'tikka'],
            'rings' => ['earring', 'jhumka', 'necklace', 'bangle', 'bracelet', 'tikka'],
            'bangles' => ['earring', 'jhumka', 'necklace', 'ring', 'tikka'],
            'bracelets' => ['earring', 'jhumka', 'necklace', 'ring', 'tikka'],
            'maang-tikka' => ['earring', 'jhumka', 'necklace', 'bangle', 'bracelet', 'ring'],
            'sets' => ['earring', 'jhumka', 'ring', 'bangle', 'bracelet', 'tikka'],
            'bridal' => ['earring', 'jhumka', 'ring', 'bangle', 'bracelet', 'tikka'],
        ];

        if (! str_contains($path, 'public/assets/images') && ! str_contains($path, 'assets/images')) {
            return false;
        }

        foreach ($conflicts[$slug] ?? [] as $needle) {
            if (str_contains($path, $needle)) {
                return true;
            }
        }

        $needles = $hints[$slug] ?? [];
        if ($needles === []) {
            return false;
        }

        foreach ($needles as $needle) {
            if (str_contains($path, $needle)) {
                return false;
            }
        }

        return true;
    }

    public static function isPlaceholderCategory(?string $name, ?string $slug): bool
    {
        foreach ([$name, $slug] as $value) {
            $value = strtolower(trim((string) $value));
            if ($value === '') {
                continue;
            }
            if (preg_match('/^test\d*$/', $value)) {
                return true;
            }
            if (in_array($value, ['jewellery', 'jewelry', 'all', 'uncategorized', 'general'], true)) {
                return true;
            }
        }

        return trim((string) $name) === '' && trim((string) $slug) === '';
    }

    private function typeSlugFromName(?string $name): ?string
    {
        $hay = strtolower((string) $name);
        if ($hay === '') {
            return null;
        }

        foreach ([
            'jhumka' => 'jhumkas',
            'earring' => 'earrings',
            'necklace' => 'necklaces',
            'choker' => 'necklaces',
            'bangle' => 'bangles',
            'bracelet' => 'bracelets',
            'tikka' => 'maang-tikka',
            'ring' => 'rings',
            'set' => 'sets',
        ] as $needle => $slug) {
            if (str_contains($hay, $needle)) {
                return $slug;
            }
        }

        return null;
    }

    private function typeKey(Product $product): ?string
    {
        $slug = strtolower(trim((string) ($product->category?->slug ?? '')));
        $name = (string) ($product->category?->name ?? '');

        if ($slug !== '' && ! self::isPlaceholderCategory($name, $slug)) {
            return $slug;
        }

        return $this->typeSlugFromName($product->name);
    }

    private function isSameStorefrontType(Product $left, Product $right): bool
    {
        $a = $this->typeKey($left);
        $b = $this->typeKey($right);
        if ($a && $b) {
            return in_array($b, $this->expandTypeSlugs([$a]), true)
                || in_array($a, $this->expandTypeSlugs([$b]), true);
        }

        return $left->category_id && $left->category_id === $right->category_id;
    }

    private function namePatternForType(?string $type): ?string
    {
        return match ($type) {
            'jhumkas' => '%jhumka%',
            'earrings' => '%earring%',
            'necklaces' => '%necklace%',
            'rings' => '%ring%',
            'bangles' => '%bangle%',
            'bracelets' => '%bracelet%',
            'maang-tikka' => '%tikka%',
            'sets', 'bridal' => '%set%',
            default => null,
        };
    }

    private function applySameTypeConstraint(Builder $query, Product $product): void
    {
        $type = $this->typeKey($product);
        $placeholder = self::isPlaceholderCategory($product->category?->name, $product->category?->slug);
        $slugs = $type ? $this->expandTypeSlugs([$type]) : [];
        $pattern = $this->namePatternForType($type);

        $query->where(function (Builder $q) use ($product, $placeholder, $slugs, $pattern) {
            $matched = false;
            if (! $placeholder && $product->category_id) {
                $q->orWhere('category_id', $product->category_id);
                $matched = true;
            }
            if ($slugs !== []) {
                $q->orWhereHas('category', fn (Builder $category) => $category->whereIn('slug', $slugs));
                $matched = true;
            }
            if ($pattern) {
                $q->orWhere('name', 'like', $pattern);
                $matched = true;
            }
            if (! $matched) {
                $q->whereRaw('0 = 1');
            }
        });
    }

    private function sameTypeQuery(Product $product): Builder
    {
        $query = Product::query()->storefront();
        $this->applySameTypeConstraint($query, $product);

        return $query;
    }

    /**
     * @param  list<string>  $types
     * @return list<string>
     */
    private function expandTypeSlugs(array $types): array
    {
        $slugs = [];
        foreach ($types as $type) {
            $key = strtolower(trim((string) $type));
            if ($key === '') {
                continue;
            }

            $slugs[] = $key;
            if ($key === 'earrings') {
                $slugs[] = 'jhumkas';
            } elseif ($key === 'jhumkas') {
                $slugs[] = 'earrings';
            } elseif ($key === 'sets' || $key === 'bridal-sets') {
                $slugs[] = 'sets';
                $slugs[] = 'bridal';
            }
        }

        return array_values(array_unique($slugs));
    }

    private function matchesType(Product $product, string $key): bool
    {
        $slug = $this->typeKey($product);
        if ($slug === $key) {
            return true;
        }
        if ($key === 'earrings' && $slug === 'jhumkas') {
            return true;
        }
        if ($key === 'jhumkas' && $slug === 'earrings') {
            return true;
        }
        if (in_array($key, ['sets', 'bridal-sets'], true)) {
            return $slug === 'sets' || $slug === 'bridal' || str_contains(strtolower($product->name), 'set');
        }

        return false;
    }

    private function metalKey(Product $product): ?string
    {
        $haystack = strtolower(($product->purity ?? '').' '.($product->metal ?? ''));

        if (str_contains($haystack, 'oxidized')) {
            return 'oxidized';
        }
        if (str_contains($haystack, 'silver')) {
            return 'silver-plated';
        }
        if (str_contains($haystack, 'antique')) {
            return 'antique';
        }
        if (
            str_contains($haystack, 'gold-plated')
            || str_contains($haystack, 'gold plated')
            || str_contains($haystack, '22k')
            || str_contains($haystack, '18k')
            || str_contains($haystack, '24k')
            || str_contains($haystack, 'yellow gold')
            || str_contains($haystack, 'gold')
        ) {
            return 'gold-plated';
        }

        foreach (self::METAL_KEYS as $key) {
            if (str_contains($haystack, $key) || str_contains($haystack, str_replace('-', ' ', $key))) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function metalSearchTerms(string $key): array
    {
        return match ($key) {
            'gold-plated', '22k', '18k', '24k' => ['gold-plated', 'gold plated', '22k', '18k', '24k', 'yellow gold'],
            'oxidized' => ['oxidized'],
            'silver-plated', 'platinum' => ['silver-plated', 'silver plated', 'silver'],
            'antique' => ['antique'],
            'diamond' => ['diamond', 'american diamond', 'ad'],
            default => [$key],
        };
    }

    /**
     * @return list<string>
     */
    private function textLines(?string $value): array
    {
        if (! filled($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim((string) $value)) ?: [];
        $items = array_values(array_filter(array_map('trim', $lines)));

        return $items !== [] ? $items : [trim((string) $value)];
    }

    /**
     * @return list<string>
     */
    private function stoneKeys(Product $product): array
    {
        return array_values(array_filter(self::STONE_KEYS, fn (string $key) => $this->hasStone($product, $key)));
    }

    private function hasStone(Product $product, string $key): bool
    {
        return str_contains(strtolower((string) $product->stone), $key);
    }

    /**
     * @return array{category: string, metal: string, stone: string, price: string, sort: string, view: string}
     */
    public function normalizeListingFilters(Request $request): array
    {
        $category = (string) $request->input('category', '');
        $metal = (string) $request->input('metal', '');
        $stone = (string) $request->input('stone', '');
        $price = (string) $request->input('price', '');
        $sort = (string) $request->input('sort', 'featured');
        $view = (string) $request->input('view', 'grid');

        $allowedCategories = array_values(array_unique(array_merge(
            [''],
            self::jewelleryTypeSlugs(),
            ['bridal-sets']
        )));
        $allowedMetals = array_values(array_unique(array_merge(
            [''],
            self::METAL_KEYS,
            ['22k', '18k', '24k', 'diamond', 'platinum']
        )));
        $allowedStones = ['', 'kundan', 'emerald', 'ruby', 'diamond', 'pearl'];
        $allowedPrices = ['', 'under-50000', '50000-100000', '100000-200000', '200000-plus'];
        $allowedSorts = ['featured', 'newest', 'price_low', 'price_high', 'bestselling', 'rating'];
        $allowedViews = ['grid', 'list'];

        return [
            'category' => in_array($category, $allowedCategories, true) ? $category : '',
            'metal' => in_array($metal, $allowedMetals, true) ? $metal : '',
            'stone' => in_array($stone, $allowedStones, true) ? $stone : '',
            'price' => in_array($price, $allowedPrices, true) ? $price : '',
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'featured',
            'view' => in_array($view, $allowedViews, true) ? $view : 'grid',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function listingHero(StorefrontCollection $collection, string $listingUrl): array
    {
        $details = trim((string) $collection->description);

        return [
            'label' => $collection->name,
            'heading_line_1' => 'Discover',
            'heading_line_2' => $collection->name,
            'description' => $details !== ''
                ? $details
                : 'Discover our stunning range of fashion jewellery including kundan, bridal and everyday pieces.',
            'cta_label' => 'Explore Collection',
            'cta_url' => $listingUrl.'#collection-products',
            'image' => $collection->image
                ? storefront_image($collection->image)
                : asset('public/assets/images/collections/bridal/hero.jpg'),
            'image_alt' => $collection->name,
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function listingTopServices(): array
    {
        return [
            ['icon' => 'bi-heart', 'title' => 'Skin-friendly', 'subtitle' => 'Anti-tarnish finish'],
            ['icon' => 'bi-box-seam', 'title' => 'Secure Packaging', 'subtitle' => 'Packed with care'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-stars', 'title' => 'Quality-checked', 'subtitle' => 'Premium finish'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function listingTrustServices(): array
    {
        return [
            ['icon' => 'bi-heart', 'title' => 'Skin-friendly', 'subtitle' => 'Anti-tarnish finish'],
            ['icon' => 'bi-stars', 'title' => 'Premium Plating', 'subtitle' => 'Lasting Shine'],
            ['icon' => 'bi-hand-index-thumb', 'title' => 'Handcrafted by', 'subtitle' => 'Expert Artisans'],
            ['icon' => 'bi-globe2', 'title' => 'Secure & Insured', 'subtitle' => 'Worldwide Shipping'],
            ['icon' => 'bi-gift', 'title' => 'Elegant Gift', 'subtitle' => 'Packaging'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function listingPromos(): array
    {
        return [
            [
                'theme' => 'dark',
                'heading' => "Visit Our\nShowroom",
                'description' => 'See our bridal jewellery in person and get help choosing from the collection.',
                'cta_label' => 'Find the Store',
                'cta_url' => route('pages.store-locator'),
                'image' => 'public/assets/images/collections/bridal/promo-custom.jpg',
                'image_alt' => 'Gold jewellery on display',
            ],
            [
                'theme' => 'light',
                'heading' => "Complete Your\nLook",
                'description' => 'From jewellery to accessories, find everything for your perfect look.',
                'cta_label' => 'Explore Collections',
                'cta_url' => route('collections.kundan'),
                'image' => 'public/assets/images/collections/bridal/promo-look.jpg',
                'image_alt' => 'Jewellery look inspiration',
            ],
        ];
    }

    /**
     * @param  array{category: string, metal: string, stone: string, price: string, sort: string}  $filters
     * @return array<string, mixed>
     */
    public function filtersFromBridal(array $filters): array
    {
        $category = $filters['category'] ?? '';
        $type = [];
        if ($category === 'bridal-sets') {
            $type = ['sets', 'bridal'];
        } elseif ($category !== '') {
            $type = [$category];
        }

        $min = null;
        $max = null;
        match ($filters['price'] ?? '') {
            'under-50000' => $max = 49999,
            '50000-100000' => [$min, $max] = [50000, 100000],
            '100000-200000' => [$min, $max] = [100000, 200000],
            '200000-plus' => $min = 200001,
            default => null,
        };

        return [
            'type' => $type,
            'metal' => filled($filters['metal'] ?? null) ? [$filters['metal']] : [],
            'stone' => filled($filters['stone'] ?? null) ? [$filters['stone']] : [],
            'min_price' => $min,
            'max_price' => $max,
            'sort' => $filters['sort'] ?? 'featured',
        ];
    }

    /**
     * @param  array{category: list<string>, metal: list<string>, stone: list<string>, occasion: list<string>, min_price: int|null, max_price: int|null, sort: string}  $filters
     * @return array<string, mixed>
     */
    public function filtersFromNewArrivals(array $filters): array
    {
        $type = [];
        $stone = $filters['stone'] ?? [];

        foreach ($filters['category'] ?? [] as $category) {
            if ($category === 'bridal') {
                $type[] = 'sets';
            } elseif ($category === 'mangalsutra') {
                $type[] = 'maang-tikka';
            } elseif ($category === 'kundan') {
                $stone[] = 'kundan';
            } elseif ($category === 'diamond') {
                $stone[] = 'diamond';
            } else {
                $type[] = $category;
            }
        }

        $occasion = [];
        foreach ($filters['occasion'] ?? [] as $item) {
            $occasion[] = match ($item) {
                'festival' => 'festive',
                'daily-wear' => 'daily',
                default => $item,
            };
        }

        return [
            'type' => array_values(array_unique($type)),
            'metal' => $filters['metal'] ?? [],
            'stone' => array_values(array_unique($stone)),
            'occasion' => $occasion,
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'sort' => $filters['sort'] ?? 'newest',
        ];
    }
}
