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

        $filters = $this->catalog->normalizeListingFilters($request);
        $paginator = $this->catalog->paginateKundan($this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.collections.kundan', [
            'products' => $products,
            'filters' => $filters,
            'categoryNav' => $this->categoryNav($filters),
            'filterCounts' => $this->catalog->filterCounts('kundan'),
            'whyFeatures' => $this->whyKundanFeatures(),
            'viewMode' => $filters['view'] ?? 'grid',
            'hero' => $this->hero(),
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

        $filters = $this->catalog->normalizeListingFilters($request);
        $paginator = $this->catalog->paginateCollection($collection->slug, $this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));
        $details = trim((string) $collection->description);
        $listingUrl = StorefrontCatalogService::storefrontUrl($collection);
        $heroImage = $collection->image
            ? storefront_image($collection->image)
            : 'public/assets/images/collections/kundan/hero.jpg';

        return view('frontend.collections.show', [
            'collection' => $collection,
            'products' => $products,
            'filters' => $filters,
            'listingUrl' => $listingUrl,
            'filterCounts' => $this->catalog->filterCounts($collection->slug),
            'emptyMessage' => $this->emptyMessage($request),
            'hero' => [
                'heading' => $collection->name,
                'subtitle' => 'Handcrafted jewellery, curated for you.',
                'description' => $details !== ''
                    ? $details
                    : 'Discover our stunning range of jewellery including necklaces, earrings, bangles, rings and complete sets.',
                'image' => $heroImage,
                'image_alt' => $collection->name,
            ],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => $collection->name, 'url' => null],
            ],
        ]);
    }

    private function emptyMessage(Request $request): string
    {
        if ($request->hasAny(['category', 'metal', 'stone', 'price', 'type', 'min_price', 'max_price'])) {
            return 'No products match your filters.';
        }

        return 'Products for this collection will appear here once they are assigned in the admin.';
    }

    /**
     * @param  array{category: string, type?: list<string>}  $filters
     * @return list<array<string, mixed>>
     */
    private function categoryNav(array $filters): array
    {
        $active = $filters['category'] !== '' ? $filters['category'] : null;

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
        $collection = \App\Models\Collection::query()->where('slug', 'kundan')->first();
        $image = $collection?->image
            ? storefront_image($collection->image)
            : 'public/assets/images/collections/kundan/hero.jpg';
        $description = trim((string) ($collection?->description ?? ''));

        return [
            'heading' => $collection?->name ?: 'Kundan Collection',
            'subtitle' => 'Timeless Heritage. Royal Elegance.',
            'description' => $description !== ''
                ? $description
                : 'Discover our exquisite range of handcrafted Kundan jewellery that celebrates tradition, craftsmanship and timeless beauty.',
            'image' => $image,
            'image_alt' => $collection?->name ?: 'Premium Kundan necklace and earrings on emerald draped fabric',
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
