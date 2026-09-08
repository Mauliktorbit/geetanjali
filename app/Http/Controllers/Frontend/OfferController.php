<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OfferController extends Controller
{
    /**
     * Offers / promotions page.
     * Demo offer data until an Offer Eloquent model exists.
     */
    public function index(Request $request): View
    {
        $category = (string) $request->input('category', 'all');
        $allowed = ['all', 'bank', 'gold', 'diamond', 'festival', 'seasonal'];
        if (! in_array($category, $allowed, true)) {
            $category = 'all';
        }

        $offers = $this->demoOffers()
            ->when($category !== 'all', fn (Collection $c) => $c->where('category', $category))
            ->values();

        return view('frontend.offers.index', [
            'offers' => $offers,
            'activeCategory' => $category,
            'filterTabs' => $this->filterTabs(),
            'benefits' => $this->benefits(),
            'terms' => $this->terms(),
            'hero' => [
                'title' => 'Exciting Offers',
                'subtitle' => 'For Every Celebration',
                'description' => 'Discover amazing deals and exclusive offers on gold, diamond and kundan jewellery.',
                'image' => 'public/assets/images/offers/hero.jpg',
                'image_alt' => 'Gold bangles and emerald jewellery on emerald silk',
            ],
            'goldClub' => [
                'kicker' => 'Not a Member Yet?',
                'title' => 'Join Geetanjali Gold Club',
                'description' => 'Enjoy exclusive offers, priority service, special discounts and much more.',
                'cta_label' => 'Join Now →',
                'cta_url' => route('register'),
                'image' => 'public/assets/images/offers/gold-card.jpg',
            ],
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Offers', 'url' => null],
            ],
        ]);
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function filterTabs(): array
    {
        return [
            ['key' => 'all', 'label' => 'All Offers'],
            ['key' => 'bank', 'label' => 'Bank Offers'],
            ['key' => 'gold', 'label' => 'Gold Offers'],
            ['key' => 'diamond', 'label' => 'Diamond Offers'],
            ['key' => 'festival', 'label' => 'Festival Offers'],
            ['key' => 'seasonal', 'label' => 'Seasonal Offers'],
        ];
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
     * @return Collection<int, array<string, mixed>>
     */
    private function demoOffers(): Collection
    {
        return collect([
            [
                'id' => 1,
                'theme' => 'dark',
                'category' => 'diamond',
                'label' => 'Flat',
                'discount_value' => '10%',
                'discount_suffix' => 'Off',
                'title' => 'On Diamond Jewellery',
                'promo_code' => 'GEET10',
                'image' => 'public/assets/images/offers/diamond.jpg',
                'image_alt' => 'Diamond jewellery offer',
                'valid_until' => '31 May 2026',
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'theme' => 'light',
                'category' => 'gold',
                'label' => 'Up To',
                'discount_value' => '15%',
                'discount_suffix' => 'Off',
                'title' => "On Making Charges\nof Gold Jewellery",
                'promo_code' => 'GOLD15',
                'image' => 'public/assets/images/offers/gold.jpg',
                'image_alt' => 'Gold jewellery making charges offer',
                'valid_until' => '31 May 2026',
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'theme' => 'dark',
                'category' => 'festival',
                'label' => 'Flat',
                'discount_value' => '5%',
                'discount_suffix' => 'Off',
                'title' => "On Kundan\nCollections",
                'promo_code' => 'KUNDAN5',
                'image' => 'public/assets/images/offers/kundan.jpg',
                'image_alt' => 'Kundan jewellery offer',
                'valid_until' => '31 May 2026',
                'sort_order' => 3,
            ],
            [
                'id' => 4,
                'theme' => 'light',
                'category' => 'seasonal',
                'label' => 'Extra',
                'discount_value' => '10%',
                'discount_suffix' => 'Off',
                'title' => "On Prepaid\nOrders",
                'promo_code' => 'PREPAID10',
                'image' => 'public/assets/images/offers/prepaid.jpg',
                'image_alt' => 'Prepaid order gift offer',
                'valid_until' => '31 May 2026',
                'sort_order' => 4,
            ],
            [
                'id' => 5,
                'theme' => 'dark',
                'category' => 'bank',
                'label' => 'Extra',
                'discount_value' => '5%',
                'discount_suffix' => 'Off',
                'title' => "With Select\nBank Cards",
                'promo_code' => 'BANK5',
                'image' => 'public/assets/images/categories/rings.jpg',
                'image_alt' => 'Bank offer on jewellery',
                'valid_until' => '30 Jun 2026',
                'sort_order' => 5,
            ],
            [
                'id' => 6,
                'theme' => 'light',
                'category' => 'gold',
                'label' => 'Flat',
                'discount_value' => '8%',
                'discount_suffix' => 'Off',
                'title' => "On 22K Gold\nJewellery",
                'promo_code' => 'GOLD8',
                'image' => 'public/assets/images/products/traditional-gold-necklace.jpg',
                'image_alt' => '22K gold jewellery offer',
                'valid_until' => '31 Jul 2026',
                'sort_order' => 6,
            ],
            [
                'id' => 7,
                'theme' => 'dark',
                'category' => 'diamond',
                'label' => 'Up To',
                'discount_value' => '12%',
                'discount_suffix' => 'Off',
                'title' => "On Diamond\nSolitaires",
                'promo_code' => 'SOLITAIRE12',
                'image' => 'public/assets/images/categories/rings.jpg',
                'image_alt' => 'Diamond solitaire offer',
                'valid_until' => '15 Aug 2026',
                'sort_order' => 7,
            ],
            [
                'id' => 8,
                'theme' => 'light',
                'category' => 'festival',
                'label' => 'Special',
                'discount_value' => '20%',
                'discount_suffix' => 'Off',
                'title' => "Festival Making\nCharges Offer",
                'promo_code' => 'FEST20',
                'image' => 'public/assets/images/occasions/festival.jpg',
                'image_alt' => 'Festival jewellery offer',
                'valid_until' => '31 Oct 2026',
                'sort_order' => 8,
            ],
        ])->sortBy('sort_order')->values();
    }
}
