<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage.
     * Static demo data is structured for easy replacement with Eloquent later.
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
        return [
            ['name' => 'Rings', 'image' => 'public/assets/images/categories/rings.jpg', 'url' => route('products.new-arrivals', ['category' => 'rings'])],
            ['name' => 'Earrings', 'image' => 'public/assets/images/categories/earrings.jpg', 'url' => route('collections.kundan', ['category' => 'earrings'])],
            ['name' => 'Necklaces', 'image' => 'public/assets/images/categories/necklaces.jpg', 'url' => route('collections.kundan', ['category' => 'necklaces'])],
            ['name' => 'Bangles', 'image' => 'public/assets/images/categories/bangles.jpg', 'url' => route('collections.kundan', ['category' => 'bangles'])],
            ['name' => 'Mangalsutra', 'image' => 'public/assets/images/categories/mangalsutra.jpg', 'url' => route('collections.bridal')],
            ['name' => 'Bridal', 'image' => 'public/assets/images/categories/bridal.jpg', 'url' => route('collections.bridal')],
            ['name' => 'Kundan', 'image' => 'public/assets/images/categories/kundan.jpg', 'url' => route('collections.kundan')],
            ['name' => 'Diamond', 'image' => 'public/assets/images/categories/diamond.jpg', 'url' => route('offers.index')],
        ];
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
        $kundan = [
            [
                'id' => 101,
                'name' => 'Heritage Kundan Set',
                'slug' => 'heritage-kundan-set',
                'image' => 'public/assets/images/categories/kundan.jpg',
                'price' => 62999,
                'compare_at_price' => 71999,
                'discount_label' => '12% Off',
                'badge' => 'Bestseller',
                'url' => route('collections.kundan'),
            ],
            [
                'id' => 102,
                'name' => 'Kundan Emerald Drop Earrings',
                'slug' => 'kundan-emerald-drop-earrings',
                'image' => 'public/assets/images/products/gold-drop-earrings.jpg',
                'price' => 24999,
                'compare_at_price' => 28999,
                'discount_label' => '14% Off',
                'url' => route('products.show', 'kundan-emerald-drop-earrings'),
            ],
            [
                'id' => 103,
                'name' => 'Royal Kundan Necklace',
                'slug' => 'royal-kundan-necklace',
                'image' => 'public/assets/images/products/traditional-gold-necklace.jpg',
                'price' => 89999,
                'compare_at_price' => 99999,
                'discount_label' => '10% Off',
                'url' => route('collections.kundan'),
            ],
            [
                'id' => 104,
                'name' => 'Polki Kundan Ring',
                'slug' => 'polki-kundan-ring',
                'image' => 'public/assets/images/categories/rings.jpg',
                'price' => 18999,
                'compare_at_price' => 21999,
                'discount_label' => '14% Off',
                'url' => route('collections.kundan'),
            ],
        ];

        $bridal = [
            [
                'id' => 201,
                'name' => 'Bridal Kundan Necklace Set',
                'slug' => 'bridal-kundan-necklace-set',
                'image' => 'public/assets/images/categories/bridal.jpg',
                'price' => 125999,
                'compare_at_price' => 145999,
                'discount_label' => '14% Off',
                'badge' => 'Bridal',
                'url' => route('collections.bridal'),
            ],
            [
                'id' => 202,
                'name' => 'Temple Bridal Jhumkas',
                'slug' => 'temple-bridal-jhumkas',
                'image' => 'public/assets/images/categories/earrings.jpg',
                'price' => 34999,
                'compare_at_price' => 39999,
                'discount_label' => '13% Off',
                'url' => route('collections.bridal'),
            ],
            [
                'id' => 203,
                'name' => 'Bridal Gold Bangle Pair',
                'slug' => 'bridal-gold-bangle-pair',
                'image' => 'public/assets/images/products/classic-gold-bangle.jpg',
                'price' => 78999,
                'compare_at_price' => 88999,
                'discount_label' => '11% Off',
                'url' => route('collections.bridal'),
            ],
            [
                'id' => 204,
                'name' => 'Wedding Maang Tikka Set',
                'slug' => 'wedding-maang-tikka-set',
                'image' => 'public/assets/images/products/gold-floral-pendant.jpg',
                'price' => 28999,
                'compare_at_price' => 33999,
                'discount_label' => '15% Off',
                'url' => route('collections.bridal'),
            ],
        ];

        $newArrivals = [
            [
                'id' => 301,
                'name' => 'New Season Gold Pendant',
                'slug' => 'new-season-gold-pendant',
                'image' => 'public/assets/images/products/gold-floral-pendant.jpg',
                'price' => 45999,
                'compare_at_price' => 52999,
                'discount_label' => '13% Off',
                'badge' => 'New',
                'url' => route('products.new-arrivals'),
            ],
            [
                'id' => 302,
                'name' => 'Fresh Drop Pearl Earrings',
                'slug' => 'fresh-drop-pearl-earrings',
                'image' => 'public/assets/images/products/gold-drop-earrings.jpg',
                'price' => 21999,
                'compare_at_price' => 25999,
                'discount_label' => '15% Off',
                'badge' => 'New',
                'url' => route('products.new-arrivals'),
            ],
            [
                'id' => 303,
                'name' => 'Modern Gold Necklace',
                'slug' => 'modern-gold-necklace',
                'image' => 'public/assets/images/categories/necklaces.jpg',
                'price' => 67999,
                'compare_at_price' => 75999,
                'discount_label' => '11% Off',
                'badge' => 'New',
                'url' => route('products.new-arrivals'),
            ],
            [
                'id' => 304,
                'name' => 'Lightweight Daily Wear Ring',
                'slug' => 'lightweight-daily-wear-ring',
                'image' => 'public/assets/images/categories/rings.jpg',
                'price' => 15999,
                'compare_at_price' => 18999,
                'discount_label' => '16% Off',
                'badge' => 'New',
                'url' => route('products.new-arrivals'),
            ],
        ];

        return [
            'kundan' => $kundan,
            'bridal' => $bridal,
            'new-arrivals' => $newArrivals,
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
            ['name' => 'Festival', 'image' => 'public/assets/images/occasions/festival.jpg', 'url' => route('offers.index')],
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
            ['icon' => 'bi-people', 'title' => 'Trusted by Thousands', 'subtitle' => 'Happy Customers'],
            ['icon' => 'bi-hourglass-split', 'title' => '30+ Years of Trust', 'subtitle' => 'Legacy of Excellence'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function testimonials(): array
    {
        return [
            [
                'rating' => 5,
                'review' => 'The Kundan set I bought for my wedding was breathtaking. Exquisite craftsmanship and truly premium finish.',
                'name' => 'Priya Sharma',
                'city' => 'Mumbai',
            ],
            [
                'rating' => 5,
                'review' => 'Beautiful gold jewellery with transparent pricing. The team helped me choose the perfect anniversary gift.',
                'name' => 'Ananya Mehta',
                'city' => 'Pune',
            ],
            [
                'rating' => 5,
                'review' => 'Elegant designs, secure packaging and wonderful service. Geetanjali has become our family jeweller.',
                'name' => 'Neha Kapoor',
                'city' => 'Delhi',
            ],
        ];
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
