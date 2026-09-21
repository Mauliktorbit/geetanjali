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

        $listingUrl = route('collections.bridal');
        $filters = $this->catalog->normalizeListingFilters($request);
        $paginator = $this->catalog->paginateCollection('bridal', $this->catalog->filtersFromBridal($filters));
        $products = $paginator->through(fn ($product) => $this->catalog->toCard($product));

        return view('frontend.collections.bridal', [
            'products' => $products,
            'filters' => $filters,
            'categoryOptions' => StorefrontCatalogService::jewelleryTypeOptions(),
            'listingUrl' => $listingUrl,
            'canonicalUrl' => url('/bridal-collection'),
            'pageTitle' => 'Bridal Jewellery Collection | Geetanjali Jewellers',
            'metaDescription' => "Explore Geetanjali Jewellers' bridal jewellery collection featuring Kundan bridal sets, gold necklaces, earrings, bangles, rings and traditional wedding jewellery.",
            'introHeading' => 'Bridal Jewellery Collection',
            'introText' => 'Discover our stunning range of bridal jewellery including necklaces, earrings, bangles, rings and complete bridal sets.',
            'showBridalSets' => true,
            'hero' => [
                'label' => 'Bridal Collection',
                'heading_line_1' => 'Timeless Beauty for',
                'heading_line_2' => 'Your Special Day',
                'description' => 'Exquisite bridal jewellery crafted in gold, kundan and diamonds to make your wedding moments truly unforgettable.',
                'cta_label' => 'Explore Collection',
                'cta_url' => $listingUrl.'#collection-products',
                'image' => asset('public/assets/images/collections/bridal/hero.jpg'),
                'image_alt' => 'Indian bride in traditional bridal jewellery',
            ],
            'promos' => StorefrontCatalogService::listingPromos(),
            'viewMode' => $filters['view'],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Bridal Collection', 'url' => null],
            ],
        ]);
    }
}
