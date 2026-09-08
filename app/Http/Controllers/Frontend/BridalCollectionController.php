<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BridalCollectionController extends Controller
{
    private const PER_PAGE = 12;

    /**
     * Bridal Collection listing with toolbar filters, sort and pagination.
     * Demo catalog until Product Eloquent models exist.
     */
    public function index(Request $request): View
    {
        $filters = $this->normalizeFilters($request);
        $catalog = $this->demoCatalog();
        $filtered = $this->applyFilters($catalog, $filters);
        $sorted = $this->applySort($filtered, $filters['sort']);
        $products = $this->paginate($sorted, $request);

        return view('frontend.collections.bridal', [
            'products' => $products,
            'filters' => $filters,
            'hero' => $this->hero(),
            'topServices' => $this->topServices(),
            'trustServices' => $this->trustServices(),
            'promos' => $this->promos(),
            'viewMode' => $filters['view'],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Bridal Collection', 'url' => null],
            ],
        ]);
    }

    /**
     * @return array{
     *     category: string,
     *     metal: string,
     *     stone: string,
     *     price: string,
     *     sort: string,
     *     view: string
     * }
     */
    private function normalizeFilters(Request $request): array
    {
        $category = (string) $request->input('category', '');
        $metal = (string) $request->input('metal', '');
        $stone = (string) $request->input('stone', '');
        $price = (string) $request->input('price', '');
        $sort = (string) $request->input('sort', 'featured');
        $view = (string) $request->input('view', 'grid');

        $allowedCategories = ['', 'bridal-sets', 'necklaces', 'earrings', 'bangles', 'rings', 'maang-tikka', 'jhumkas'];
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
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @param  array{category: string, metal: string, stone: string, price: string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $catalog, array $filters): Collection
    {
        return $catalog
            ->when($filters['category'] !== '', fn (Collection $c) => $c->where('type', $filters['category']))
            ->when($filters['metal'] !== '', fn (Collection $c) => $c->where('metal_key', $filters['metal']))
            ->when($filters['stone'] !== '', fn (Collection $c) => $c->filter(
                fn (array $p) => in_array($filters['stone'], $p['stones'], true)
            ))
            ->when($filters['price'] !== '', function (Collection $c) use ($filters) {
                return $c->filter(function (array $p) use ($filters) {
                    $price = (int) $p['price'];

                    return match ($filters['price']) {
                        'under-50000' => $price < 50000,
                        '50000-100000' => $price >= 50000 && $price <= 100000,
                        '100000-200000' => $price >= 100000 && $price <= 200000,
                        '200000-plus' => $price > 200000,
                        default => true,
                    };
                });
            })
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
            'bestselling' => $products->sortByDesc('sold')->values(),
            default => $products->sortByDesc(fn (array $p) => ($p['is_featured'] ? 1000 : 0) + ($p['sold'] ?? 0))->values(),
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
     * @return array<string, string>
     */
    private function hero(): array
    {
        return [
            'label' => 'Bridal Collection',
            'heading_line_1' => 'Timeless Beauty for',
            'heading_line_2' => 'Your Special Day',
            'description' => 'Exquisite bridal jewellery crafted in gold, kundan and diamonds to make your wedding moments truly unforgettable.',
            'cta_label' => 'Explore Collection',
            'cta_url' => route('collections.bridal') . '#bridal-products',
            'image' => 'public/assets/images/collections/bridal/hero.jpg',
            'image_alt' => 'Indian bride in traditional bridal jewellery',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function topServices(): array
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
    private function trustServices(): array
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
    private function promos(): array
    {
        return [
            [
                'theme' => 'dark',
                'heading' => "Custom Bridal\nJewellery",
                'description' => 'Create your dream jewellery with our customization service.',
                'cta_label' => 'Book Appointment',
                'cta_url' => route('contact'),
                'image' => 'public/assets/images/collections/bridal/promo-custom.jpg',
                'image_alt' => 'Handcrafted bridal necklace detail',
            ],
            [
                'theme' => 'light',
                'heading' => "Complete Your\nBridal Look",
                'description' => 'From jewellery to accessories, find everything for your perfect bridal look.',
                'cta_label' => 'Explore Accessories',
                'cta_url' => route('collections.kundan'),
                'image' => 'public/assets/images/collections/bridal/promo-look.jpg',
                'image_alt' => 'Bridal jewellery look inspiration',
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function demoCatalog(): Collection
    {
        $images = [
            'public/assets/images/categories/bridal.jpg',
            'public/assets/images/products/traditional-gold-necklace.jpg',
            'public/assets/images/categories/kundan.jpg',
            'public/assets/images/banners/kundan-collection.jpg',
            'public/assets/images/products/classic-gold-bangle.jpg',
            'public/assets/images/products/gold-drop-earrings.jpg',
            'public/assets/images/categories/mangalsutra.jpg',
            'public/assets/images/categories/rings.jpg',
            'public/assets/images/categories/necklaces.jpg',
            'public/assets/images/categories/earrings.jpg',
            'public/assets/images/categories/bangles.jpg',
            'public/assets/images/products/gold-floral-pendant.jpg',
            'public/assets/images/products/gallery/main.jpg',
            'public/assets/images/occasions/wedding.jpg',
            'public/assets/images/occasions/engagement.jpg',
            'public/assets/images/hero/hero-2.jpg',
        ];

        $rows = [
            ['Royal Kundan Bridal Set', 'bridal-sets', '22k', ['kundan', 'emerald'], 2500000, true, 4.9, 24, true],
            ['Heritage Gold Necklace Set', 'necklaces', '22k', ['kundan'], 389500, true, 4.8, 31, true],
            ['Traditional Temple Set', 'bridal-sets', '22k', ['ruby', 'kundan'], 475000, false, 4.7, 18, true],
            ['Diamond Kundan Set', 'bridal-sets', '18k', ['diamond', 'kundan'], 625000, true, 4.9, 42, true],
            ['Bridal Gold Bangles', 'bangles', '22k', ['kundan'], 245000, false, 4.6, 27, false],
            ['Polki Jhumka Earrings', 'jhumkas', '22k', ['kundan', 'pearl'], 135000, true, 4.8, 36, false],
            ['Kundan Maang Tikka', 'maang-tikka', '22k', ['kundan', 'pearl'], 85000, false, 4.7, 22, false],
            ['Bridal Cocktail Ring', 'rings', '18k', ['diamond', 'emerald'], 95000, false, 4.5, 15, false],
            ['Emerald Bridal Necklace', 'necklaces', '22k', ['emerald', 'kundan'], 512000, true, 4.8, 19, true],
            ['Ruby Bridal Earrings', 'earrings', '22k', ['ruby'], 168000, false, 4.6, 14, false],
            ['Pearl Bridal Set', 'bridal-sets', '18k', ['pearl', 'kundan'], 398000, false, 4.4, 11, false],
            ['Platinum Bridal Band', 'rings', 'platinum', ['diamond'], 125000, false, 4.5, 9, false],
            ['Temple Gold Necklace', 'necklaces', '22k', ['ruby'], 445000, true, 4.7, 21, true],
            ['Diamond Stud Earrings', 'earrings', '18k', ['diamond'], 210000, false, 4.6, 28, false],
            ['Heavy Bridal Bangle Pair', 'bangles', '22k', ['kundan', 'emerald'], 320000, true, 4.8, 16, true],
            ['Classic Bridal Jhumkas', 'jhumkas', '22k', ['kundan'], 98000, false, 4.5, 20, false],
        ];

        return collect($rows)->values()->map(function (array $row, int $index) use ($images) {
            [$name, $type, $metalKey, $stones, $price, $isFeatured, $rating, $reviews, $isBestseller] = $row;

            $metalLabels = [
                '22k' => '22K Gold',
                '18k' => '18K Gold',
                'diamond' => 'Diamond',
                'platinum' => 'Platinum',
            ];

            return [
                'id' => 100 + $index + 1,
                'slug' => \Illuminate\Support\Str::slug($name),
                'name' => $name,
                'type' => $type,
                'metal_key' => $metalKey,
                'metal' => $metalLabels[$metalKey] ?? $metalKey,
                'stones' => $stones,
                'price' => $price,
                'compare_at_price' => null,
                'discount_label' => null,
                'badge' => $isBestseller ? 'BESTSELLER' : null,
                'is_featured' => $isFeatured,
                'is_bestseller' => $isBestseller,
                'rating' => $rating,
                'review_count' => $reviews,
                'sold' => $reviews * 4,
                'image' => $images[$index % count($images)],
                'url' => route('products.show', 'kundan-emerald-drop-earrings'),
                'created_at' => now()->subDays(40 - $index)->timestamp,
            ];
        });
    }
}
