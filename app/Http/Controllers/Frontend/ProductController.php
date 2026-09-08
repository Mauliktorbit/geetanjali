<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a product details page.
     * Uses structured demo data until Product models/migrations are introduced.
     */
    public function show(string $slug): View
    {
        $product = $this->findDemoProduct($slug);

        abort_if($product === null, 404);

        $relatedProducts = $this->relatedProducts($product->id);

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Shop', 'url' => route('products.new-arrivals')],
                ['label' => $product->category, 'url' => route('collections.kundan')],
                ['label' => $product->name, 'url' => null],
            ],
        ]);
    }

    /**
     * UI-ready delivery check endpoint (demo response until backend exists).
     */
    public function checkDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pincode' => ['required', 'string', 'min:6', 'max:10'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $pincode = preg_replace('/\D+/', '', $validated['pincode']);
        $available = strlen((string) $pincode) === 6;

        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Delivery available to '.$pincode
                : 'Delivery not available for this pincode',
            'estimated_delivery' => $available ? '3–5 business days' : null,
        ]);
    }

    /**
     * Add product to session cart.
     */
    public function addToCart(Request $request, CartService $cart): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'name' => ['nullable', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'discount_label' => ['nullable', 'string', 'max:40'],
            'metal' => ['nullable', 'string', 'max:80'],
            'weight' => ['nullable', 'string', 'max:80'],
            'url' => ['nullable', 'string', 'max:255'],
        ]);

        $added = $cart->add((int) $validated['product_id'], (int) $validated['quantity'], $validated);

        if (! $added) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $cart->count(),
            'product_id' => (int) $validated['product_id'],
            'quantity' => (int) $validated['quantity'],
        ]);
    }

    private function findDemoProduct(string $slug): ?object
    {
        return $this->demoCatalog()->firstWhere('slug', $slug);
    }

    /**
     * @return Collection<int, object>
     */
    private function relatedProducts(int $excludeId): Collection
    {
        return $this->demoCatalog()
            ->where('id', '!=', $excludeId)
            ->take(6)
            ->values();
    }

    /**
     * Structured demo catalogue — replace with Eloquent Product::query() later.
     *
     * @return Collection<int, object>
     */
    private function demoCatalog(): Collection
    {
        return collect([
            (object) [
                'id' => 1,
                'slug' => 'kundan-emerald-drop-earrings',
                'name' => 'Kundan Emerald Drop Earrings',
                'sku' => 'GJ-KE-2201',
                'category' => 'Earrings',
                'badge' => 'Best Seller',
                'price' => 124500,
                'sale_price' => null,
                'tax_note' => 'Inclusive of all taxes',
                'short_description' => 'Exquisite kundan earrings crafted in 22K gold with emerald drops and hand-set stones. A perfect blend of tradition and timeless elegance.',
                'description' => 'These Kundan Emerald Drop Earrings are a masterpiece of traditional craftsmanship, handcrafted in 22K yellow gold with certified Kundan stones and vibrant emerald drops.',
                'metal' => '22K Yellow Gold',
                'purity' => '22K',
                'stone' => 'Emerald, Kundan',
                'style' => 'Traditional',
                'weight' => '18.350 g (Approx)',
                'dimensions' => 'Length 4.2 cm',
                'occasion' => 'Bridal, Festive',
                'certification' => 'BIS Hallmarked',
                'stock' => 8,
                'stock_status' => 'in_stock',
                'rating' => 4.8,
                'review_count' => 32,
                'sold_count' => '120+',
                'images' => [
                    (object) ['url' => 'public/assets/images/products/gallery/main.jpg', 'alt' => 'Kundan Emerald Drop Earrings front view'],
                    (object) ['url' => 'public/assets/images/products/gallery/closeup.jpg', 'alt' => 'Kundan Emerald Drop Earrings close-up'],
                    (object) ['url' => 'public/assets/images/products/gallery/detail.jpg', 'alt' => 'Kundan Emerald Drop Earrings detail'],
                    (object) ['url' => 'public/assets/images/products/gallery/packaging.jpg', 'alt' => 'Geetanjali jewellery packaging'],
                ],
                'highlights' => [
                    'Handcrafted in 22K Hallmarked Gold',
                    'Studded with authentic Kundan stones and Emerald drops',
                    'Secure screw-back closure for added comfort',
                    'Comes with Geetanjali Jewellers authenticity certificate',
                    'Perfect for bridal and festive occasions',
                ],
                'benefits' => [
                    ['icon' => 'bi-circle', 'label' => '22K Pure Gold'],
                    ['icon' => 'bi-award', 'label' => 'Hallmarked Jewellery'],
                    ['icon' => 'bi-arrow-left-right', 'label' => 'Lifetime Exchange'],
                    ['icon' => 'bi-patch-check', 'label' => 'Certified Jewellery'],
                ],
                'reviews' => [
                    (object) [
                        'name' => 'Priya Sharma',
                        'rating' => 5,
                        'date' => '12 Jul 2026',
                        'text' => 'Absolutely stunning earrings. The emerald drops catch the light beautifully and the finish feels truly premium.',
                    ],
                    (object) [
                        'name' => 'Ananya Mehta',
                        'rating' => 5,
                        'date' => '02 Jul 2026',
                        'text' => 'Bought these for my sister’s wedding. Craftsmanship and packaging were excellent.',
                    ],
                    (object) [
                        'name' => 'Neha Kapoor',
                        'rating' => 4,
                        'date' => '18 Jun 2026',
                        'text' => 'Elegant traditional design. Comfortable to wear for long festive evenings.',
                    ],
                ],
                'rating_breakdown' => [
                    5 => 24,
                    4 => 6,
                    3 => 2,
                    2 => 0,
                    1 => 0,
                ],
            ],
            (object) [
                'id' => 2,
                'slug' => 'polki-jhumka-earrings',
                'name' => 'Polki Jhumka Earrings',
                'category' => 'Earrings',
                'price' => 108900,
                'rating' => 4.7,
                'review_count' => 21,
                'image' => 'public/assets/images/products/gold-drop-earrings.jpg',
            ],
            (object) [
                'id' => 3,
                'slug' => 'gold-floral-pendant-set',
                'name' => 'Gold Floral Pendant Set',
                'category' => 'Necklaces',
                'price' => 45999,
                'rating' => 4.6,
                'review_count' => 18,
                'image' => 'public/assets/images/products/gold-floral-pendant.jpg',
            ],
            (object) [
                'id' => 4,
                'slug' => 'classic-gold-bangle',
                'name' => 'Classic Gold Bangle',
                'category' => 'Bangles',
                'price' => 38999,
                'rating' => 4.5,
                'review_count' => 27,
                'image' => 'public/assets/images/products/classic-gold-bangle.jpg',
            ],
            (object) [
                'id' => 5,
                'slug' => 'traditional-gold-necklace',
                'name' => 'Traditional Gold Necklace',
                'category' => 'Necklaces',
                'price' => 89999,
                'rating' => 4.8,
                'review_count' => 34,
                'image' => 'public/assets/images/products/traditional-gold-necklace.jpg',
            ],
            (object) [
                'id' => 6,
                'slug' => 'heritage-kundan-set',
                'name' => 'Heritage Kundan Set',
                'category' => 'Kundan',
                'price' => 62999,
                'rating' => 4.9,
                'review_count' => 41,
                'image' => 'public/assets/images/categories/kundan.jpg',
            ],
            (object) [
                'id' => 7,
                'slug' => 'diamond-stud-earrings',
                'name' => 'Diamond Stud Earrings',
                'category' => 'Diamond',
                'price' => 32999,
                'rating' => 4.4,
                'review_count' => 15,
                'image' => 'public/assets/images/categories/earrings.jpg',
            ],
        ]);
    }
}
