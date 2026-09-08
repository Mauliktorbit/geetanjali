<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewArrivalsController extends Controller
{
    private const PER_PAGE = 12;

    /**
     * New Arrivals listing with filters, sort and pagination.
     * Demo catalog until Product Eloquent models exist.
     */
    public function index(Request $request): View
    {
        $filters = $this->normalizeFilters($request);
        $catalog = $this->demoCatalog();
        $filtered = $this->applyFilters($catalog, $filters);
        $sorted = $this->applySort($filtered, $filters['sort']);
        $products = $this->paginate($sorted, $request);

        return view('frontend.products.new-arrivals', [
            'products' => $products,
            'filters' => $filters,
            'viewMode' => $filters['view'],
            'services' => $this->services(),
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'New Arrivals', 'url' => null],
            ],
            'hero' => [
                'title' => 'New Arrivals',
                'subtitle' => 'Discover our latest additions to the collection',
                'image' => 'public/assets/images/collections/new-arrivals/hero.jpg',
                'image_alt' => 'Latest gold and kundan jewellery arrivals',
            ],
        ]);
    }

    /**
     * @return array{
     *     category: list<string>,
     *     metal: list<string>,
     *     stone: list<string>,
     *     occasion: list<string>,
     *     min_price: int|null,
     *     max_price: int|null,
     *     sort: string,
     *     view: string
     * }
     */
    private function normalizeFilters(Request $request): array
    {
        $allowedCategories = ['rings', 'earrings', 'necklaces', 'bangles', 'mangalsutra', 'bridal', 'kundan', 'diamond'];
        $allowedMetals = ['18k', '22k', '24k'];
        $allowedStones = ['diamond', 'emerald', 'ruby', 'kundan', 'pearl'];
        $allowedOccasions = ['wedding', 'engagement', 'festival', 'daily-wear'];
        $allowedSorts = ['newest', 'price_low', 'price_high', 'popularity', 'rating', 'name_asc', 'name_desc'];
        $allowedViews = ['grid', 'list'];

        $category = array_values(array_intersect((array) $request->input('category', []), $allowedCategories));
        $metal = array_values(array_intersect((array) $request->input('metal', []), $allowedMetals));
        $stone = array_values(array_intersect((array) $request->input('stone', []), $allowedStones));
        $occasion = array_values(array_intersect((array) $request->input('occasion', []), $allowedOccasions));

        $min = $request->filled('min_price') ? max(0, (int) $request->input('min_price')) : null;
        $max = $request->filled('max_price') ? max(0, (int) $request->input('max_price')) : null;

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        $sort = (string) $request->input('sort', 'newest');
        $view = (string) $request->input('view', 'grid');

        return [
            'category' => $category,
            'metal' => $metal,
            'stone' => $stone,
            'occasion' => $occasion,
            'min_price' => $min,
            'max_price' => $max,
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'newest',
            'view' => in_array($view, $allowedViews, true) ? $view : 'grid',
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @param  array{category: list<string>, metal: list<string>, stone: list<string>, occasion: list<string>, min_price: int|null, max_price: int|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $catalog, array $filters): Collection
    {
        return $catalog
            ->when($filters['category'] !== [], fn (Collection $c) => $c->whereIn('category', $filters['category']))
            ->when($filters['metal'] !== [], fn (Collection $c) => $c->whereIn('metal_key', $filters['metal']))
            ->when($filters['stone'] !== [], fn (Collection $c) => $c->filter(
                fn (array $p) => count(array_intersect($p['stones'], $filters['stone'])) > 0
            ))
            ->when($filters['occasion'] !== [], fn (Collection $c) => $c->whereIn('occasion', $filters['occasion']))
            ->when($filters['min_price'] !== null, fn (Collection $c) => $c->where('price', '>=', $filters['min_price']))
            ->when($filters['max_price'] !== null, fn (Collection $c) => $c->where('price', '<=', $filters['max_price']))
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
            'popularity' => $products->sortByDesc('sold')->values(),
            'rating' => $products->sortByDesc('rating')->values(),
            'name_asc' => $products->sortBy('name')->values(),
            'name_desc' => $products->sortByDesc('name')->values(),
            default => $products->sortByDesc('created_at')->values(),
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     */
    private function paginate(Collection $products, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));

        return new LengthAwarePaginator(
            $products->forPage($page, self::PER_PAGE)->values(),
            $products->count(),
            self::PER_PAGE,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
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
     * @return Collection<int, array<string, mixed>>
     */
    private function demoCatalog(): Collection
    {
        $images = [
            'public/assets/images/products/gold-drop-earrings.jpg',
            'public/assets/images/categories/necklaces.jpg',
            'public/assets/images/categories/rings.jpg',
            'public/assets/images/categories/earrings.jpg',
            'public/assets/images/products/gold-floral-pendant.jpg',
            'public/assets/images/products/gallery/main.jpg',
            'public/assets/images/categories/kundan.jpg',
            'public/assets/images/categories/bangles.jpg',
            'public/assets/images/products/traditional-gold-necklace.jpg',
            'public/assets/images/products/classic-gold-bangle.jpg',
            'public/assets/images/categories/diamond.jpg',
            'public/assets/images/categories/bridal.jpg',
            'public/assets/images/categories/mangalsutra.jpg',
            'public/assets/images/gallery/gallery-1.jpg',
            'public/assets/images/gallery/gallery-2.jpg',
            'public/assets/images/gallery/gallery-3.jpg',
            'public/assets/images/gallery/gallery-4.jpg',
            'public/assets/images/hero/hero-3.jpg',
            'public/assets/images/banners/kundan-collection.jpg',
            'public/assets/images/occasions/engagement.jpg',
            'public/assets/images/occasions/festival.jpg',
            'public/assets/images/occasions/daily-wear.jpg',
            'public/assets/images/products/gallery/closeup.jpg',
            'public/assets/images/products/gallery/detail.jpg',
        ];

        $rows = [
            ['Emerald Floral Earrings', 'earrings', '22k', ['emerald'], 'festival', 132000, 4.8, 18],
            ['Kundan Emerald Necklace', 'necklaces', '22k', ['kundan', 'emerald'], 'wedding', 248900, 4.9, 24],
            ['Diamond Floral Ring', 'rings', '18k', ['diamond'], 'engagement', 89500, 4.7, 15],
            ['Polki Kundan Jhumkas', 'earrings', '22k', ['kundan'], 'wedding', 108900, 4.8, 32],
            ['Pearl Drop Pendant', 'necklaces', '18k', ['pearl'], 'daily-wear', 64500, 4.5, 12],
            ['Diamond Tear Drop Earrings', 'earrings', '18k', ['diamond'], 'engagement', 156000, 4.9, 21],
            ['Ruby Kundan Choker', 'kundan', '22k', ['ruby', 'kundan'], 'wedding', 198700, 4.6, 17],
            ['Classic Gold Bangle Pair', 'bangles', '22k', ['kundan'], 'festival', 124500, 4.7, 28],
            ['Emerald Cocktail Ring', 'rings', '18k', ['emerald'], 'festival', 72500, 4.4, 9],
            ['Temple Gold Necklace', 'necklaces', '22k', ['ruby'], 'wedding', 312000, 4.8, 19],
            ['Diamond Stud Earrings', 'diamond', '18k', ['diamond'], 'daily-wear', 98000, 4.6, 26],
            ['Bridal Mangalsutra', 'mangalsutra', '22k', ['diamond'], 'wedding', 175300, 4.9, 34],
            ['Meenakari Kundan Set', 'bridal', '22k', ['kundan'], 'wedding', 425000, 5.0, 14],
            ['Floral Gold Pendant', 'necklaces', '22k', ['pearl'], 'daily-wear', 48500, 4.3, 11],
            ['Emerald Drop Jhumkas', 'earrings', '22k', ['emerald'], 'festival', 86700, 4.5, 16],
            ['Solitaire Diamond Ring', 'rings', '18k', ['diamond'], 'engagement', 265000, 4.9, 22],
            ['Antique Kundan Necklace', 'kundan', '22k', ['kundan', 'pearl'], 'wedding', 289500, 4.7, 13],
            ['Lightweight Gold Bangles', 'bangles', '22k', ['kundan'], 'daily-wear', 96500, 4.4, 20],
            ['Pearl Cluster Earrings', 'earrings', '18k', ['pearl'], 'festival', 54200, 4.5, 10],
            ['Ruby Floral Ring', 'rings', '22k', ['ruby'], 'festival', 67800, 4.3, 8],
            ['Diamond Pendant Set', 'diamond', '18k', ['diamond'], 'engagement', 215000, 4.8, 18],
            ['Heritage Bridal Set', 'bridal', '22k', ['emerald', 'kundan'], 'wedding', 512000, 5.0, 27],
            ['Daily Wear Gold Chain', 'necklaces', '22k', ['pearl'], 'daily-wear', 78500, 4.2, 14],
            ['Festive Kundan Tikka', 'kundan', '22k', ['kundan', 'pearl'], 'festival', 42800, 4.6, 23],
        ];

        return collect($rows)->values()->map(function (array $row, int $index) use ($images) {
            [$name, $category, $metalKey, $stones, $occasion, $price, $rating, $reviews] = $row;

            return [
                'id' => 200 + $index + 1,
                'slug' => Str::slug($name),
                'name' => $name,
                'category' => $category,
                'metal_key' => $metalKey,
                'stones' => $stones,
                'occasion' => $occasion,
                'price' => $price,
                'compare_at_price' => null,
                'discount_label' => null,
                'badge' => 'NEW',
                'is_new_arrival' => true,
                'rating' => $rating,
                'review_count' => $reviews,
                'sold' => $reviews * 3,
                'image' => $images[$index % count($images)],
                'url' => route('products.show', 'kundan-emerald-drop-earrings'),
                'created_at' => now()->subDays($index)->timestamp,
            ];
        });
    }
}
