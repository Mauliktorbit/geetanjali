<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewArrivalsController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    /**
     * New Arrivals listing from products assigned to the new-arrivals collection.
     */
    public function index(Request $request): View
    {
        abort_unless(StorefrontCatalogService::isActiveSlug('new-arrivals'), 404);

        $filters = $this->normalizeFilters($request);
        $paginator = $this->catalog->paginateCollection('new-arrivals', $this->catalog->filtersFromNewArrivals($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.products.new-arrivals', [
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => StorefrontCatalogService::jewelleryTypeOptions(),
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
        $allowedCategories = array_values(array_unique(array_merge(
            StorefrontCatalogService::jewelleryTypeSlugs(),
            ['mangalsutra', 'bridal', 'kundan', 'diamond']
        )));
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
     * @return list<array<string, string>>
     */
    private function services(): array
    {
        return [
            ['icon' => 'bi-heart', 'title' => 'Skin-friendly', 'subtitle' => 'Anti-tarnish finish'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
            ['icon' => 'bi-box-seam', 'title' => 'Secure Packaging', 'subtitle' => 'Packed with care'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-stars', 'title' => 'Quality-checked', 'subtitle' => 'Premium finish'],
            ['icon' => 'bi-gift', 'title' => 'Gift Wrapping', 'subtitle' => 'Available on Request'],
        ];
    }
}
