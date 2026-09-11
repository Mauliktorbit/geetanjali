<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Collection as StorefrontCollection;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StorefrontCatalogService
{
    public const PER_PAGE = 12;

    public const MIN_PRICE = 2000;

    public const MAX_PRICE = 500000;

    public const TYPE_KEYS = ['necklaces', 'earrings', 'rings', 'bangles', 'maang-tikka', 'bracelets', 'sets', 'jhumkas'];

    public const METAL_KEYS = ['22k', '18k'];

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
            ->get(['id', 'name', 'slug', 'image']);

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

        return [
            'type' => $type,
            'metal' => $metal,
            'stone' => $stone,
            'min_price' => $min,
            'max_price' => $max,
            'sort' => $sort,
            'category' => $request->filled('category') ? (string) $request->input('category') : null,
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
     * @return array{type: array<string, int>, metal: array<string, int>, stone: array<string, int>}
     */
    public function filterCounts(?string $collectionSlug = 'kundan'): array
    {
        $catalog = Product::query()->inCollection($collectionSlug)->with('category')->get();

        $type = [];
        foreach (self::jewelleryTypeSlugs() as $key) {
            $type[$key] = $catalog->filter(fn (Product $p) => $this->typeKey($p) === $key)->count();
        }

        $metal = [];
        foreach (self::METAL_KEYS as $key) {
            $metal[$key] = $catalog->filter(fn (Product $p) => $this->metalKey($p) === $key)->count();
        }

        $stone = [];
        foreach (self::STONE_KEYS as $key) {
            $stone[$key] = $catalog->filter(fn (Product $p) => $this->hasStone($p, $key))->count();
        }

        return compact('type', 'metal', 'stone');
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
                'relatedProducts' => fn ($q) => $q->storefront()->with('inventories'),
            ])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function relatedCards(Product $product, int $limit = 6): Collection
    {
        $related = $product->relatedProducts
            ->filter(fn (Product $item) => $item->is_active && ! $item->is_archived)
            ->take($limit);

        if ($related->count() < $limit) {
            $extra = Product::query()
                ->storefront()
                ->whereKeyNot($product->id)
                ->when($related->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $related->pluck('id')))
                ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
                ->with(['inventories'])
                ->latest()
                ->limit($limit - $related->count())
                ->get();

            $related = $related->concat($extra);
        }

        return $related->map(fn (Product $item) => $this->toCard($item))->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function toCard(Product $product): array
    {
        $price = (float) $product->effective_price;
        $compare = (float) $product->regular_price;
        $discount = ($compare > $price && $compare > 0)
            ? 'Save ₹'.number_format($compare - $price)
            : null;
        $stock = $this->availableStock($product);

        return [
            'id' => (int) $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'type' => $this->typeKey($product),
            'metal_key' => $this->metalKey($product),
            'metal' => $product->metal,
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
            'image' => $product->main_image ?: 'public/assets/images/categories/kundan.jpg',
            'weight' => $this->weightLabel($product),
            'url' => route('products.show', $product->slug),
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

        return (object) [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category?->name ?: 'Jewellery',
            'badge' => $this->badge($product),
            'price' => $price,
            'sale_price' => $sale && $sale > 0 && $sale < $price ? $sale : null,
            'tax_note' => $product->tax_note ?: 'Inclusive of all taxes',
            'short_description' => $product->short_description,
            'description' => $product->description,
            'metal' => $product->metal,
            'purity' => $product->purity,
            'stone' => $product->stone,
            'style' => $product->style,
            'weight' => $this->weightLabel($product),
            'dimensions' => $this->dimensionsLabel($product),
            'occasion' => $product->occasion,
            'certification' => $product->certification,
            'stock' => $stock,
            'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
            'rating' => (float) ($product->avg_rating ?: 0),
            'review_count' => (int) ($product->review_count ?: $reviews->count()),
            'sold_count' => $sold >= 100 ? $sold.'+' : (string) $sold,
            'image' => $images[0]->url ?? 'public/assets/images/categories/kundan.jpg',
            'images' => $images,
            'highlights' => array_values(array_filter($product->highlights ?? [])),
            'benefits' => $this->benefits($product),
            'reviews' => $reviews,
            'rating_breakdown' => $breakdown,
            'estimated_delivery' => $product->estimated_delivery ?: '3–5 business days',
        ];
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['type'])) {
            $query->whereHas('category', fn (Builder $q) => $q->whereIn('slug', $filters['type']));
        }

        if (! empty($filters['metal'])) {
            $query->where(function (Builder $q) use ($filters) {
                foreach ($filters['metal'] as $metal) {
                    $q->orWhere('purity', 'like', '%'.$metal.'%')
                        ->orWhere('metal', 'like', '%'.$metal.'%');
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
            $paths[] = 'public/assets/images/categories/kundan.jpg';
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
            ['icon' => 'bi-circle', 'label' => $product->purity ? $product->purity.' Pure Gold' : 'Certified Gold'],
            ['icon' => 'bi-award', 'label' => $product->certification ?: 'Hallmarked Jewellery'],
            ['icon' => 'bi-arrow-left-right', 'label' => 'Lifetime Exchange'],
            ['icon' => 'bi-patch-check', 'label' => 'Certified Jewellery'],
        ];
    }

    private function typeKey(Product $product): ?string
    {
        $slug = $product->category?->slug;

        return filled($slug) ? $slug : null;
    }

    private function metalKey(Product $product): ?string
    {
        $haystack = strtolower(($product->purity ?? '').' '.($product->metal ?? ''));

        foreach (self::METAL_KEYS as $key) {
            if (str_contains($haystack, $key)) {
                return $key;
            }
        }

        return null;
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
        $allowedMetals = ['', '22k', '18k', 'diamond', 'platinum'];
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
                : 'Discover our stunning range of jewellery crafted in gold, kundan and diamonds.',
            'cta_label' => 'Explore Collection',
            'cta_url' => $listingUrl.'#bridal-products',
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
            ['icon' => 'bi-patch-check', 'title' => '100% Hallmarked', 'subtitle' => 'Certified Jewellery'],
            ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-gem', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function listingTrustServices(): array
    {
        return [
            ['icon' => 'bi-diamond', 'title' => 'Certified Natural', 'subtitle' => 'Diamonds & Gemstones'],
            ['icon' => 'bi-award', 'title' => 'BIS Hallmarked', 'subtitle' => 'Pure Gold'],
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
                'heading' => "Custom\nJewellery",
                'description' => 'Create your dream jewellery with our customization service.',
                'cta_label' => 'Book Appointment',
                'cta_url' => route('contact'),
                'image' => 'public/assets/images/collections/bridal/promo-custom.jpg',
                'image_alt' => 'Handcrafted jewellery detail',
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
            $type = ['sets'];
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
