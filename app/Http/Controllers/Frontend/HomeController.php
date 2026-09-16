<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    /**
     * Display the public homepage.
     */
    public function index(): View
    {
        return view('frontend.home', [
            'heroSlides' => $this->heroSlides(),
            'categories' => $this->categories(),
            'services' => $this->services(),
            'bestSellerTabs' => $this->bestSellerTabs(),
            'productsByTab' => $this->productsByTab(),
            'occasions' => $this->occasions(),
            'kundanBanner' => $this->kundanBanner(),
            'whyFeatures' => $this->whyFeatures(),
            'testimonials' => $this->testimonials(),
            'galleryImages' => $this->galleryImages(),
        ]);
    }

    /**
     * @return list<array<string, string>>
     */
    private function heroSlides(): array
    {
        return [
            [
                'image' => 'public/assets/images/hero/hero-1.jpg',
                'image_alt' => 'Luxury Kundan necklace and earrings on emerald backdrop',
                'kicker' => '',
                'title_line_1' => 'Timeless Beauty.',
                'title_line_2' => 'Crafted to Perfection.',
                'description' => 'Discover our exclusive collection of Kundan, Gold & Diamond Jewellery.',
                'cta_label' => 'Shop Now',
                'cta_url' => route('home') . '#bestsellers',
            ],
            [
                'image' => 'public/assets/images/hero/hero-2.jpg',
                'image_alt' => 'Royal kundan jewellery set',
                'kicker' => '',
                'title_line_1' => 'Royal Kundan.',
                'title_line_2' => 'Made for Generations.',
                'description' => 'Handcrafted Kundan pieces that celebrate tradition with modern grace.',
                'cta_label' => 'Explore Kundan',
                'cta_url' => route('collections.kundan'),
            ],
            [
                'image' => 'public/assets/images/hero/hero-3.jpg',
                'image_alt' => 'Elegant gold jewellery details',
                'kicker' => '',
                'title_line_1' => 'Elegance in',
                'title_line_2' => 'Every Detail.',
                'description' => 'From bridal grandeur to everyday sparkle — jewellery designed to be cherished.',
                'cta_label' => 'Shop Now',
                'cta_url' => route('products.new-arrivals'),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function categories(): array
    {
        return StorefrontCatalogService::jewelleryTypes()->map(function ($category) {
            return [
                'name' => $category->name,
                'image' => StorefrontCatalogService::categoryImage($category->slug, $category->image ?? null),
                'url' => route('collections.kundan', ['category' => $category->slug]),
            ];
        })->all();
    }

    /**
     * @return list<array<string, string>>
     */
    private function services(): array
    {
        return [
            ['icon' => 'bi-patch-check', 'title' => 'Certified Jewellery', 'subtitle' => '100% Hallmarked'],
            ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
            ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders'],
            ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
            ['icon' => 'bi-gem', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
            ['icon' => 'bi-gift', 'title' => 'Gift Wrapping', 'subtitle' => 'Available on Request'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function bestSellerTabs(): array
    {
        return [
            [
                'key' => 'kundan',
                'label' => 'Kundan Collection',
                'view_all_url' => route('collections.kundan'),
                'view_all_label' => 'View All Kundan Collection',
            ],
            [
                'key' => 'bridal',
                'label' => 'Bridal Collection',
                'view_all_url' => route('collections.bridal'),
                'view_all_label' => 'View All Bridal Collection',
            ],
            [
                'key' => 'new-arrivals',
                'label' => 'New Arrivals',
                'view_all_url' => route('products.new-arrivals'),
                'view_all_label' => 'View All New Arrivals',
            ],
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function productsByTab(): array
    {
        return [
            'kundan' => $this->catalog->homeCards('kundan'),
            'bridal' => $this->catalog->homeCards('bridal'),
            'new-arrivals' => $this->catalog->homeCards('new-arrivals'),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function occasions(): array
    {
        return [
            ['name' => 'Wedding', 'image' => 'public/assets/images/occasions/wedding.jpg', 'url' => route('collections.bridal')],
            ['name' => 'Engagement', 'image' => 'public/assets/images/occasions/engagement.jpg', 'url' => route('collections.bridal')],
            ['name' => 'Festival', 'image' => 'public/assets/images/occasions/festival.jpg', 'url' => route('products.new-arrivals')],
            ['name' => 'Daily Wear', 'image' => 'public/assets/images/occasions/daily-wear.jpg', 'url' => route('products.new-arrivals')],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function kundanBanner(): array
    {
        return [
            'image' => 'public/assets/images/banners/kundan-collection.jpg',
            'image_alt' => 'Exclusive handcrafted Kundan jewellery',
            'title_line_1' => 'EXCLUSIVE KUNDAN',
            'title_line_2' => 'COLLECTION',
            'description' => 'Handcrafted Heritage. Made for Modern You.',
            'cta_label' => 'Explore Collection',
            'cta_url' => route('collections.kundan'),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function whyFeatures(): array
    {
        return [
            ['icon' => 'bi-award', 'title' => '100% Hallmarked', 'subtitle' => 'Certified Jewellery'],
            ['icon' => 'bi-receipt', 'title' => 'Transparent Pricing', 'subtitle' => 'No Hidden Charges'],
            ['icon' => 'bi-people', 'title' => 'Ahmedabad Showroom', 'subtitle' => 'PNTC Tower, Vejalpur'],
            ['icon' => 'bi-hourglass-split', 'title' => 'Made in Our Workshop', 'subtitle' => 'Kundan from design to setting'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function testimonials(): array
    {
        $demoNames = ['Priya Sharma', 'Ananya Mehta', 'Neha Kapoor'];

        $fromAdmin = \App\Models\Testimonial::query()
            ->where('is_active', true)
            ->whereNotIn('name', $demoNames)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        if ($fromAdmin->isNotEmpty()) {
            return $fromAdmin->map(fn ($item) => [
                'rating' => (int) ($item->rating ?: 5),
                'review' => $item->content,
                'name' => $item->name,
                'city' => $item->designation ?: 'Verified customer',
            ])->all();
        }

        $fromReviews = \App\Models\Review::query()
            ->where('status', 'approved')
            ->whereNotIn('customer_name', $demoNames)
            ->latest('id')
            ->limit(6)
            ->get();

        if ($fromReviews->isNotEmpty()) {
            return $fromReviews->map(fn ($item) => [
                'rating' => (int) ($item->rating ?: 5),
                'review' => $item->comment ?: $item->title,
                'name' => $item->customer_name ?: 'Customer',
                'city' => $item->is_verified_purchase ? 'Verified purchase' : 'Geetanjali customer',
            ])->all();
        }

        return [];
    }

    /**
     * @return list<array<string, string>>
     */
    private function galleryImages(): array
    {
        $instagram = config('brand.social.instagram');

        return [
            ['src' => 'public/assets/images/gallery/gallery-1.jpg', 'alt' => 'Gold jewellery detail', 'url' => $instagram],
            ['src' => 'public/assets/images/gallery/gallery-2.jpg', 'alt' => 'Diamond ring close-up', 'url' => $instagram],
            ['src' => 'public/assets/images/gallery/gallery-3.jpg', 'alt' => 'Elegant earrings', 'url' => $instagram],
            ['src' => 'public/assets/images/gallery/gallery-4.jpg', 'alt' => 'Statement necklace', 'url' => $instagram],
            ['src' => 'public/assets/images/gallery/gallery-5.jpg', 'alt' => 'Gold bangles', 'url' => $instagram],
            ['src' => 'public/assets/images/gallery/gallery-6.jpg', 'alt' => 'Diamond jewellery sparkle', 'url' => $instagram],
        ];
    }
}
