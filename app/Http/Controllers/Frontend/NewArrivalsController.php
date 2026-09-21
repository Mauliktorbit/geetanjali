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

        $filters = $this->catalog->normalizeListingFilters($request);
        if (! $request->filled('sort')) {
            $filters['sort'] = 'newest';
        }

        $paginator = $this->catalog->paginateCollection('new-arrivals', $this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.products.new-arrivals', [
            'products' => $products,
            'filters' => $filters,
            'filterCounts' => $this->catalog->filterCounts('new-arrivals'),
            'listingUrl' => route('products.new-arrivals'),
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
