<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Collection as StorefrontCollection;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    public function kundan(Request $request): View
    {
        abort_unless(StorefrontCatalogService::isActiveSlug('kundan'), 404);

        $bounds = $this->catalog->priceBounds('kundan');
        $filters = $this->catalog->normalizeFilters($request, $bounds['min'], $bounds['max']);
        $paginator = $this->catalog->paginateKundan($filters);
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.collections.kundan', [
            'products' => $products,
            'filters' => $filters,
            'filterCounts' => $this->catalog->filterCounts('kundan'),
            'categoryNav' => $this->categoryNav($filters),
            'services' => $this->services(),
            'whyFeatures' => $this->whyKundanFeatures(),
            'viewMode' => $filters['view'] ?? 'grid',
            'hero' => $this->hero(),
            'minPriceBound' => $bounds['min'],
            'maxPriceBound' => $bounds['max'],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Kundan Collection', 'url' => null],
            ],
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        if ($slug === 'kundan') {
            return redirect()->route('collections.kundan', $request->query());
        }

        if ($slug === 'bridal') {
            return redirect()->route('collections.bridal', $request->query());
        }

        if ($slug === 'new-arrivals') {
            return redirect()->route('products.new-arrivals', $request->query());
        }

        $collection = StorefrontCollection::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $listingUrl = url('/collection/'.$collection->slug);
        $filters = $this->catalog->normalizeListingFilters($request);
        $paginator = $this->catalog->paginateCollection($slug, $this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));
        $details = trim((string) $collection->description);

        return view('frontend.collections.bridal', [
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => StorefrontCatalogService::jewelleryTypeOptions(),
            'listingUrl' => $listingUrl,
            'canonicalUrl' => $listingUrl,
            'pageTitle' => $collection->name.' | Geetanjali Jewellers',
            'metaDescription' => $details !== ''
                ? $details
                : 'Explore '.$collection->name.' jewellery from Geetanjali Jewellers.',
            'introHeading' => $collection->name,
            'introText' => $details !== ''
                ? $details
                : 'Discover our stunning range of jewellery including necklaces, earrings, bangles, rings and complete sets.',
            'showBridalSets' => false,
            'hero' => StorefrontCatalogService::listingHero($collection, $listingUrl),
            'promos' => StorefrontCatalogService::listingPromos(),
            'viewMode' => $filters['view'],
            'emptyMessage' => $this->emptyMessage($request),
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => $collection->name, 'url' => null],
            ],
        ]);
    }

    private function emptyMessage(Request $request): string
    {
        if ($request->hasAny(['category', 'metal', 'stone', 'price'])) {
            return 'No products match your filters.';
        }

        return 'Products for this collection will appear here once they are assigned in the admin.';
    }

    /**
     * @param  array{category: string|null, type: list<string>}  $filters
     * @return list<array<string, mixed>>
     */
    private function categoryNav(array $filters): array
    {
        $active = $filters['category'] ?? ($filters['type'][0] ?? null);

        return StorefrontCatalogService::jewelleryTypes()->map(function ($category) use ($active) {
            $key = $category->slug;

            return [
                'key' => $key,
                'label' => $category->name,
                'image' => StorefrontCatalogService::categoryImage($key, $category->image ?? null),
                'active' => $active === $key,
                'url' => route('collections.kundan', ['category' => $key]).'#collection-products',
            ];
        })->all();
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
            ['icon' => 'bi-heart', 'title' => 'Skin-friendly', 'subtitle' => 'Anti-tarnish finish'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
            ['icon' => 'bi-box-seam', 'title' => 'Secure Packaging', 'subtitle' => 'Packed with care'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-stars', 'title' => 'Quality-checked', 'subtitle' => 'Premium finish'],
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
}
