<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OfferCategory;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(protected OfferService $offers) {}

    public function index(Request $request): View
    {
        $category = OfferCategory::normalize($request->query('category', 'all'));

        return view('frontend.offers.index', [
            'offers' => $this->offers->forStorefront($category),
            'highlights' => $this->highlights(),
            'activeCategory' => $category,
            'filterTabs' => OfferCategory::tabs(),
            'benefits' => $this->benefits(),
            'terms' => $this->terms(),
            'hero' => [
                'title' => 'Exciting Offers',
                'subtitle' => 'For Every Celebration',
                'description' => 'Discover amazing deals and exclusive offers on gold, diamond and kundan jewellery.',
                'image' => 'public/assets/images/offers/hero.jpg',
                'image_alt' => 'Gold bangles and emerald jewellery on emerald silk',
            ],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Offers', 'url' => null],
            ],
        ]);
    }

    /**
     * @return list<array<string, string>>
     */
    private function benefits(): array
    {
        return [
            ['icon' => 'bi-patch-check', 'title' => 'Best Prices', 'subtitle' => 'Guaranteed'],
            ['icon' => 'bi-tag', 'title' => 'Exclusive Offers', 'subtitle' => 'For Our Customers'],
            ['icon' => 'bi-gift', 'title' => 'Easy Redeem', 'subtitle' => 'Simple & Hassle Free'],
            ['icon' => 'bi-shield-lock', 'title' => '100% Secure', 'subtitle' => 'Safe & Trusted'],
            ['icon' => 'bi-clock', 'title' => 'Limited Period', 'subtitle' => "Hurry, Don't Miss Out!"],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function terms(): array
    {
        return [
            ['icon' => 'bi-x-circle', 'text' => "Offers cannot be\nclubbed together"],
            ['icon' => 'bi-award', 'text' => "Offer valid on selected\nproducts only"],
            ['icon' => 'bi-truck', 'text' => "Discounts are applied\non MRP"],
            ['icon' => 'bi-clock-history', 'text' => "Offers valid for limited\nperiod only"],
            ['icon' => 'bi-bag-check', 'text' => "Geetanjali Jewellers\nreserves the right to\nmodify/withdraw offers"],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function highlights(): array
    {
        return [
            [
                'title' => 'Kundan Collection',
                'text' => 'Handcrafted kundan sets, earrings and bridal pieces made in our workshop.',
                'cta' => 'Explore Kundan',
                'url' => route('collections.kundan'),
                'image' => 'public/assets/images/offers/kundan.jpg',
            ],
            [
                'title' => 'Bridal Jewellery',
                'text' => 'Complete bridal jewellery for the wedding day, from necklace sets to bangles.',
                'cta' => 'View Bridal',
                'url' => route('collections.bridal'),
                'image' => 'public/assets/images/occasions/wedding.jpg',
            ],
            [
                'title' => 'New Arrivals',
                'text' => 'See the latest gold, kundan and diamond pieces added to the store.',
                'cta' => 'Shop New Arrivals',
                'url' => route('products.new-arrivals'),
                'image' => 'public/assets/images/offers/gold.jpg',
            ],
            [
                'title' => 'Visit Our Showroom',
                'text' => 'Try pieces in person at our Ahmedabad showroom. Mon–Sat, 10 AM to 7 PM.',
                'cta' => 'Get Directions',
                'url' => route('contact'),
                'image' => 'public/assets/images/offers/diamond.jpg',
            ],
        ];
    }
}
