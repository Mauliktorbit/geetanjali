<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BridalCollectionController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    /**
     * Bridal Collection listing from products assigned to the bridal collection.
     */
    public function index(Request $request): View
    {
        abort_unless(StorefrontCatalogService::isActiveSlug('bridal'), 404);

        $collection = \App\Models\Collection::query()->where('slug', 'bridal')->first();
        $details = trim((string) ($collection?->description ?? ''));
        $listingUrl = route('collections.bridal');
        $filters = $this->catalog->normalizeListingFilters($request);
        $paginator = $this->catalog->paginateCollection('bridal', $this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.collections.bridal', [
            'products' => $products,
            'filters' => $filters,
            'filterCounts' => $this->catalog->filterCounts('bridal'),
            'listingUrl' => $listingUrl,
            'canonicalUrl' => url('/bridal-collection'),
            'pageTitle' => ($collection?->seo_title ?: 'Bridal Jewellery Collection').' | Geetanjali Jewellers',
            'metaDescription' => $collection?->seo_description
                ?: "Explore Geetanjali Jewellers' bridal jewellery collection featuring Kundan bridal sets, necklaces, earrings, bangles, rings and traditional wedding jewellery.",
            'introHeading' => $collection?->name ?: 'Bridal Jewellery Collection',
            'introText' => $details !== ''
                ? $details
                : 'Discover our stunning range of bridal jewellery including necklaces, earrings, bangles, rings and complete bridal sets.',
            'showBridalSets' => true,
            'hero' => StorefrontCatalogService::listingHero(
                $collection ?? new \App\Models\Collection(['name' => 'Bridal Collection', 'description' => $details, 'image' => null]),
                $listingUrl
            ),
            'promos' => StorefrontCatalogService::listingPromos(),
            'viewMode' => $filters['view'],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Bridal Collection', 'url' => null],
            ],
        ]);
    }
}
