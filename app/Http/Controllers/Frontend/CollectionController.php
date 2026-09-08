<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CollectionController extends Controller
{
    private const PER_PAGE = 12;

    private const MIN_PRICE = 2000;

    private const MAX_PRICE = 500000;

    /**
     * Kundan Collection listing with filters, sort and pagination.
     * Demo catalog until Product Eloquent models exist.
     */
    public function kundan(Request $request): View
    {
        $filters = $this->normalizeFilters($request);
        $catalog = $this->demoCatalog();

        $filtered = $this->applyFilters($catalog, $filters);
        $sorted = $this->applySort($filtered, $filters['sort']);

        $products = $this->paginate($sorted, $request);

        return view('frontend.collections.kundan', [
            'products' => $products,
            'filters' => $filters,
            'filterCounts' => $this->filterCounts($catalog),
            'categoryNav' => $this->categoryNav($filters),
            'services' => $this->services(),
            'whyFeatures' => $this->whyKundanFeatures(),
            'hero' => $this->hero(),
            'minPriceBound' => self::MIN_PRICE,
            'maxPriceBound' => self::MAX_PRICE,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Kundan Collection', 'url' => null],
            ],
        ]);
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
    private function normalizeFilters(Request $request): array
    {
        $type = array_values(array_filter((array) $request->input('type', [])));
        $metal = array_values(array_filter((array) $request->input('metal', [])));
        $stone = array_values(array_filter((array) $request->input('stone', [])));

        if ($request->filled('category') && $type === []) {
            $type = [(string) $request->input('category')];
        }

        $min = (int) $request->input('min_price', self::MIN_PRICE);
        $max = (int) $request->input('max_price', self::MAX_PRICE);

        $min = max(self::MIN_PRICE, min($min, self::MAX_PRICE));
        $max = max(self::MIN_PRICE, min($max, self::MAX_PRICE));

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
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @param  array{type: list<string>, metal: list<string>, stone: list<string>, min_price: int, max_price: int}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $catalog, array $filters): Collection
    {
        return $catalog
            ->when($filters['type'] !== [], fn (Collection $c) => $c->whereIn('type', $filters['type']))
            ->when($filters['metal'] !== [], fn (Collection $c) => $c->whereIn('metal_key', $filters['metal']))
            ->when($filters['stone'] !== [], fn (Collection $c) => $c->filter(
                fn (array $p) => count(array_intersect($p['stones'], $filters['stone'])) > 0
            ))
            ->filter(fn (array $p) => $p['price'] >= $filters['min_price'] && $p['price'] <= $filters['max_price'])
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array<string, mixed>>
     */
    private function applySort(Collection $products, string $sort): Collection
    {
        return match ($sort) {
            'price_low' => $products->sortBy('price')->values(),
            'price_high' => $products->sortByDesc('price')->values(),
            'newest' => $products->sortByDesc('created_at')->values(),
            'rating' => $products->sortByDesc('rating')->values(),
            default => $products->sortByDesc(fn (array $p) => ($p['is_bestseller'] ? 1000 : 0) + ($p['sold'] ?? 0))->values(),
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     */
    private function paginate(Collection $products, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));
        $total = $products->count();
        $items = $products->forPage($page, self::PER_PAGE)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @return array{type: array<string, int>, metal: array<string, int>, stone: array<string, int>}
     */
    private function filterCounts(Collection $catalog): array
    {
        $typeKeys = ['necklaces', 'earrings', 'rings', 'bangles', 'maang-tikka', 'bracelets', 'sets', 'jhumkas'];
        $metalKeys = ['22k', '18k'];
        $stoneKeys = ['emerald', 'ruby', 'pearl', 'polki', 'meenakari'];

        $type = [];
        foreach ($typeKeys as $key) {
            $type[$key] = $catalog->where('type', $key)->count();
        }

        $metal = [];
        foreach ($metalKeys as $key) {
            $metal[$key] = $catalog->where('metal_key', $key)->count();
        }

        $stone = [];
        foreach ($stoneKeys as $key) {
            $stone[$key] = $catalog->filter(fn (array $p) => in_array($key, $p['stones'], true))->count();
        }

        return compact('type', 'metal', 'stone');
    }

    /**
     * @param  array{category: string|null, type: list<string>}  $filters
     * @return list<array<string, mixed>>
     */
    private function categoryNav(array $filters): array
    {
        $active = $filters['category'] ?? ($filters['type'][0] ?? null);

        $items = [
            ['key' => 'necklaces', 'label' => 'Kundan Necklaces', 'image' => 'public/assets/images/categories/necklaces.jpg'],
            ['key' => 'earrings', 'label' => 'Kundan Earrings', 'image' => 'public/assets/images/categories/earrings.jpg'],
            ['key' => 'rings', 'label' => 'Kundan Rings', 'image' => 'public/assets/images/categories/rings.jpg'],
            ['key' => 'bangles', 'label' => 'Kundan Bangles', 'image' => 'public/assets/images/categories/bangles.jpg'],
            ['key' => 'sets', 'label' => 'Kundan Sets', 'image' => 'public/assets/images/categories/bridal.jpg'],
            ['key' => 'maang-tikka', 'label' => 'Kundan Maang Tikka', 'image' => 'public/assets/images/categories/mangalsutra.jpg'],
            ['key' => 'bracelets', 'label' => 'Kundan Bracelets', 'image' => 'public/assets/images/categories/bangles.jpg'],
            ['key' => 'jhumkas', 'label' => 'Kundan Jhumkas', 'image' => 'public/assets/images/categories/earrings.jpg'],
        ];

        return array_map(function (array $item) use ($active) {
            $item['active'] = $active === $item['key'];
            $item['url'] = route('collections.kundan', ['category' => $item['key']]);

            return $item;
        }, $items);
    }

    /**
     * @return array<string, string>
     */
    private function hero(): array
    {
        return [
            'heading' => 'Kundan Collection',
            'subtitle' => 'Timeless Heritage. Royal Elegance.',
            'description' => 'Discover our exquisite range of handcrafted Kundan jewellery that celebrates tradition, craftsmanship and timeless beauty.',
            'image' => 'public/assets/images/collections/kundan/hero.jpg',
            'image_alt' => 'Premium Kundan necklace and earrings on emerald draped fabric',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function services(): array
    {
        return [
            ['icon' => 'bi-patch-check', 'title' => '100% Hallmarked', 'subtitle' => 'Certified Jewellery'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
            ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders Above ₹10,000'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-gem', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
            ['icon' => 'bi-gift', 'title' => 'Gift Wrapping', 'subtitle' => 'Available on Request'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function whyKundanFeatures(): array
    {
        return [
            [
                'icon' => 'bi-stars',
                'title' => 'Royal Heritage',
                'subtitle' => 'A legacy of royal craftsmanship passed down through generations.',
            ],
            [
                'icon' => 'bi-hand-index-thumb',
                'title' => 'Handcrafted Artistry',
                'subtitle' => 'Each piece is meticulously handcrafted by skilled artisans.',
            ],
            [
                'icon' => 'bi-diamond',
                'title' => 'Authentic Stones',
                'subtitle' => 'We use only the finest quality Kundan stones and gems.',
            ],
            [
                'icon' => 'bi-heart',
                'title' => 'Timeless Beauty',
                'subtitle' => 'Designed to be cherished today and for generations.',
            ],
            [
                'icon' => 'bi-gift',
                'title' => 'Perfect for Every Occasion',
                'subtitle' => 'From weddings to celebrations, Kundan adds royal elegance.',
            ],
        ];
    }

    /**
     * Structured demo catalogue — replace with Eloquent later.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function demoCatalog(): Collection
    {
        $images = [
            'public/assets/images/products/traditional-gold-necklace.jpg',
            'public/assets/images/products/gold-drop-earrings.jpg',
            'public/assets/images/products/classic-gold-bangle.jpg',
            'public/assets/images/products/gold-floral-pendant.jpg',
            'public/assets/images/products/gallery/main.jpg',
            'public/assets/images/categories/kundan.jpg',
            'public/assets/images/categories/earrings.jpg',
            'public/assets/images/categories/rings.jpg',
            'public/assets/images/categories/bangles.jpg',
            'public/assets/images/categories/necklaces.jpg',
            'public/assets/images/categories/bridal.jpg',
            'public/assets/images/banners/kundan-collection.jpg',
        ];

        $rows = [
            ['Emerald Kundan Necklace Set', 'necklaces', '22k', ['emerald', 'polki'], 248900, 'BESTSELLER', true, 4.9, 48, 'emerald-kundan-necklace-set'],
            ['Ruby Kundan Drop Earrings', 'earrings', '22k', ['ruby'], 86700, null, false, 4.7, 32, 'ruby-kundan-drop-earrings'],
            ['Traditional Kundan Choker', 'necklaces', '22k', ['polki', 'pearl'], 175300, 'TRENDING', true, 4.8, 41, 'traditional-kundan-choker'],
            ['Kundan Jhumka Earrings', 'jhumkas', '22k', ['polki'], 108900, 'BESTSELLER', true, 4.9, 56, 'kundan-jhumka-earrings'],
            ['Emerald Kundan Ring', 'rings', '18k', ['emerald'], 56300, 'NEW', false, 4.6, 18, 'emerald-kundan-ring'],
            ['Kundan Meenakari Bangles', 'bangles', '22k', ['meenakari', 'polki'], 124500, null, false, 4.5, 22, 'kundan-meenakari-bangles'],
            ['Kundan Maang Tikka', 'maang-tikka', '22k', ['pearl', 'polki'], 42800, null, false, 4.7, 29, 'kundan-maang-tikka'],
            ['Kundan Bridal Set', 'sets', '22k', ['emerald', 'ruby', 'polki'], 362000, 'LIMITED', true, 5.0, 64, 'kundan-bridal-set'],
            ['Pearl Kundan Necklace', 'necklaces', '18k', ['pearl'], 189500, null, false, 4.4, 15, 'pearl-kundan-necklace'],
            ['Polki Kundan Earrings', 'earrings', '22k', ['polki'], 94500, 'BESTSELLER', true, 4.8, 37, 'polki-kundan-earrings'],
            ['Ruby Kundan Ring', 'rings', '22k', ['ruby'], 48900, null, false, 4.5, 12, 'ruby-kundan-ring'],
            ['Emerald Kundan Bangles', 'bangles', '22k', ['emerald'], 156800, 'TRENDING', false, 4.6, 21, 'emerald-kundan-bangles'],
            ['Kundan Bracelet Set', 'bracelets', '18k', ['polki', 'pearl'], 78200, null, false, 4.3, 9, 'kundan-bracelet-set'],
            ['Meenakari Kundan Set', 'sets', '22k', ['meenakari', 'ruby'], 298500, 'BESTSELLER', true, 4.9, 44, 'meenakari-kundan-set'],
            ['Classic Kundan Jhumkas', 'jhumkas', '18k', ['pearl'], 67500, 'NEW', false, 4.4, 14, 'classic-kundan-jhumkas'],
            ['Royal Emerald Kundan Set', 'sets', '22k', ['emerald'], 415000, 'LIMITED', true, 5.0, 28, 'royal-emerald-kundan-set'],
            ['Delicate Kundan Bracelet', 'bracelets', '22k', ['emerald'], 52400, null, false, 4.2, 8, 'delicate-kundan-bracelet'],
            ['Heritage Kundan Necklace', 'necklaces', '22k', ['polki', 'meenakari'], 225000, null, true, 4.7, 33, 'heritage-kundan-necklace'],
            ['Floral Kundan Earrings', 'earrings', '18k', ['ruby', 'pearl'], 71200, null, false, 4.5, 19, 'floral-kundan-earrings'],
            ['Temple Kundan Ring', 'rings', '22k', ['polki'], 38500, 'NEW', false, 4.3, 11, 'temple-kundan-ring'],
            ['Grand Kundan Bangle Pair', 'bangles', '22k', ['ruby', 'polki'], 198700, 'TRENDING', true, 4.8, 26, 'grand-kundan-bangle-pair'],
            ['Bridal Maang Tikka', 'maang-tikka', '22k', ['emerald', 'pearl'], 56900, 'BESTSELLER', true, 4.9, 39, 'bridal-maang-tikka'],
            ['Antique Kundan Choker Set', 'sets', '22k', ['polki'], 276400, null, false, 4.6, 17, 'antique-kundan-choker-set'],
            ['Lightweight Kundan Jhumkas', 'jhumkas', '18k', ['meenakari'], 45800, null, false, 4.4, 13, 'lightweight-kundan-jhumkas'],
        ];

        return collect($rows)->values()->map(function (array $row, int $index) use ($images) {
            [$name, $type, $metalKey, $stones, $price, $badge, $isBestseller, $rating, $reviews, $slug] = $row;

            $compare = $isBestseller ? (int) round($price * 1.08) : null;

            return [
                'id' => $index + 1,
                'slug' => $slug,
                'name' => $name,
                'type' => $type,
                'metal_key' => $metalKey,
                'metal' => $metalKey === '22k' ? '22K Yellow Gold' : '18K Gold',
                'stones' => $stones,
                'price' => $price,
                'compare_at_price' => $compare,
                'discount_label' => $compare ? 'Save ₹'.number_format($compare - $price) : null,
                'badge' => $badge,
                'is_bestseller' => $isBestseller,
                'rating' => $rating,
                'review_count' => $reviews,
                'sold' => $reviews * 3,
                'image' => $images[$index % count($images)],
                'url' => route('products.show', 'kundan-emerald-drop-earrings'),
                'created_at' => now()->subDays(30 - $index)->timestamp,
            ];
        });
    }
}
