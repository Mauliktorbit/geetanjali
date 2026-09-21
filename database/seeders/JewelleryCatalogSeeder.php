<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingClass;
use App\Models\TaxRate;
use App\Models\Warehouse;
use App\Services\StorefrontCatalogService;
use Illuminate\Database\Seeder;

class JewelleryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $tax = TaxRate::firstOrCreate(
            ['name' => 'GST 3%'],
            ['hsn_sac' => '7113', 'cgst' => 1.5, 'sgst' => 1.5, 'igst' => 3, 'is_active' => true]
        );
        $shipping = ShippingClass::firstOrCreate(
            ['slug' => 'standard'],
            ['name' => 'Standard', 'cost' => 0, 'is_active' => true]
        );
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            ['name' => 'Main Warehouse', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'country' => 'India', 'is_default' => true, 'is_active' => true]
        );

        $categories = [];
        foreach ([
            'necklaces' => 'Necklaces',
            'earrings' => 'Earrings',
            'rings' => 'Rings',
            'bangles' => 'Bangles',
            'maang-tikka' => 'Maang Tikka',
            'bracelets' => 'Bracelets',
            'sets' => 'Sets',
            'jhumkas' => 'Jhumkas',
        ] as $slug => $name) {
            $categories[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true, 'display_order' => count($categories) + 1]
            );
        }

        $pages = StorefrontCatalogService::ensurePageCollections()->keyBy('slug');
        $kundan = $pages->get('kundan');
        $bridal = $pages->get('bridal');
        $newArrivals = $pages->get('new-arrivals');

        $images = [
            'public/assets/images/products/traditional-gold-necklace.jpg',
            'public/assets/images/products/gold-drop-earrings.jpg',
            'public/assets/images/products/classic-gold-bangle.jpg',
            'public/assets/images/products/gold-floral-pendant.jpg',
            'public/assets/images/products/gallery/main.jpg',
            'public/assets/images/categories/kundan.jpg',
            'public/assets/images/categories/earrings.jpg',
            'public/assets/images/categories/rings.jpg',
            'public/assets/images/categories/bangles.jpg',
            'public/assets/images/categories/necklaces.jpg',
            'public/assets/images/categories/bridal.jpg',
            'public/assets/images/banners/kundan-collection.jpg',
        ];

        $rows = [
            ['GJ-KN-1001', 'Emerald Kundan Necklace Set', 'emerald-kundan-necklace-set', 'necklaces', '22K', '22K Yellow Gold', 'Emerald, Polki', 248900, 268800, 'BESTSELLER', true, 4.9, 48, 144, 42.500],
            ['GJ-KE-1002', 'Ruby Kundan Drop Earrings', 'ruby-kundan-drop-earrings', 'earrings', '22K', '22K Yellow Gold', 'Ruby', 86700, null, null, false, 4.7, 32, 96, 16.200],
            ['GJ-KN-1003', 'Traditional Kundan Choker', 'traditional-kundan-choker', 'necklaces', '22K', '22K Yellow Gold', 'Polki, Pearl', 175300, 189000, 'TRENDING', true, 4.8, 41, 123, 36.800],
            ['GJ-KJ-1004', 'Kundan Jhumka Earrings', 'kundan-jhumka-earrings', 'jhumkas', '22K', '22K Yellow Gold', 'Polki', 108900, 117600, 'BESTSELLER', true, 4.9, 56, 168, 22.100],
            ['GJ-KR-1005', 'Emerald Kundan Ring', 'emerald-kundan-ring', 'rings', '18K', '18K Gold', 'Emerald', 56300, null, 'NEW', false, 4.6, 18, 54, 6.450],
            ['GJ-KB-1006', 'Kundan Meenakari Bangles', 'kundan-meenakari-bangles', 'bangles', '22K', '22K Yellow Gold', 'Meenakari, Polki', 124500, null, null, false, 4.5, 22, 66, 28.300],
            ['GJ-KM-1007', 'Kundan Maang Tikka', 'kundan-maang-tikka', 'maang-tikka', '22K', '22K Yellow Gold', 'Pearl, Polki', 42800, null, null, false, 4.7, 29, 87, 8.120],
            ['GJ-KS-1008', 'Kundan Bridal Set', 'kundan-bridal-set', 'sets', '22K', '22K Yellow Gold', 'Emerald, Ruby, Polki', 362000, 390960, 'LIMITED', true, 5.0, 64, 192, 86.400],
            ['GJ-KN-1009', 'Pearl Kundan Necklace', 'pearl-kundan-necklace', 'necklaces', '18K', '18K Gold', 'Pearl', 189500, null, null, false, 4.4, 15, 45, 31.200],
            ['GJ-KE-1010', 'Polki Kundan Earrings', 'polki-kundan-earrings', 'earrings', '22K', '22K Yellow Gold', 'Polki', 94500, 102000, 'BESTSELLER', true, 4.8, 37, 111, 14.800],
            ['GJ-KR-1011', 'Ruby Kundan Ring', 'ruby-kundan-ring', 'rings', '22K', '22K Yellow Gold', 'Ruby', 48900, null, null, false, 4.5, 12, 36, 5.900],
            ['GJ-KB-1012', 'Emerald Kundan Bangles', 'emerald-kundan-bangles', 'bangles', '22K', '22K Yellow Gold', 'Emerald', 156800, 169300, 'TRENDING', false, 4.6, 21, 63, 33.750],
            ['GJ-KB-1013', 'Kundan Bracelet Set', 'kundan-bracelet-set', 'bracelets', '18K', '18K Gold', 'Polki, Pearl', 78200, null, null, false, 4.3, 9, 27, 12.400],
            ['GJ-KS-1014', 'Meenakari Kundan Set', 'meenakari-kundan-set', 'sets', '22K', '22K Yellow Gold', 'Meenakari, Ruby', 298500, 322400, 'BESTSELLER', true, 4.9, 44, 132, 74.200],
            ['GJ-KJ-1015', 'Classic Kundan Jhumkas', 'classic-kundan-jhumkas', 'jhumkas', '18K', '18K Gold', 'Pearl', 67500, null, 'NEW', false, 4.4, 14, 42, 11.600],
            ['GJ-KS-1016', 'Royal Emerald Kundan Set', 'royal-emerald-kundan-set', 'sets', '22K', '22K Yellow Gold', 'Emerald', 415000, 448200, 'LIMITED', true, 5.0, 28, 84, 92.100],
            ['GJ-KB-1017', 'Delicate Kundan Bracelet', 'delicate-kundan-bracelet', 'bracelets', '22K', '22K Yellow Gold', 'Emerald', 52400, null, null, false, 4.2, 8, 24, 9.850],
            ['GJ-KN-1018', 'Heritage Kundan Necklace', 'heritage-kundan-necklace', 'necklaces', '22K', '22K Yellow Gold', 'Polki, Meenakari', 225000, null, null, true, 4.7, 33, 99, 48.600],
            ['GJ-KE-1019', 'Floral Kundan Earrings', 'floral-kundan-earrings', 'earrings', '18K', '18K Gold', 'Ruby, Pearl', 71200, null, null, false, 4.5, 19, 57, 13.200],
            ['GJ-KR-1020', 'Temple Kundan Ring', 'temple-kundan-ring', 'rings', '22K', '22K Yellow Gold', 'Polki', 38500, null, 'NEW', false, 4.3, 11, 33, 5.150],
            ['GJ-KB-1021', 'Grand Kundan Bangle Pair', 'grand-kundan-bangle-pair', 'bangles', '22K', '22K Yellow Gold', 'Ruby, Polki', 198700, 214600, 'TRENDING', true, 4.8, 26, 78, 41.300],
            ['GJ-KM-1022', 'Bridal Maang Tikka', 'bridal-maang-tikka', 'maang-tikka', '22K', '22K Yellow Gold', 'Emerald, Pearl', 56900, 61450, 'BESTSELLER', true, 4.9, 39, 117, 7.840],
            ['GJ-KS-1023', 'Antique Kundan Choker Set', 'antique-kundan-choker-set', 'sets', '22K', '22K Yellow Gold', 'Polki', 276400, null, null, false, 4.6, 17, 51, 61.500],
            ['GJ-KJ-1024', 'Lightweight Kundan Jhumkas', 'lightweight-kundan-jhumkas', 'jhumkas', '18K', '18K Gold', 'Meenakari', 45800, null, null, false, 4.4, 13, 39, 10.250],
            ['GJ-KE-2201', 'Kundan Emerald Drop Earrings', 'kundan-emerald-drop-earrings', 'earrings', '22K', '22K Yellow Gold', 'Emerald, Kundan', 124500, null, 'Best Seller', true, 4.8, 32, 120, 18.350],
        ];

        foreach ($rows as $index => $row) {
            [$sku, $name, $slug, $type, $purity, $metal, $stone, $price, $compare, $badge, $bestseller, $rating, $reviews, $sold, $weight] = $row;

            $product = Product::withTrashed()
                ->where(fn ($q) => $q->where('slug', $slug)->orWhere('sku', $sku))
                ->first();
            if ($product?->trashed()) {
                $product->restore();
            }
            $payload = [
                    'name' => $name,
                    'barcode' => '890'.str_pad((string) ($index + 1), 10, '0', STR_PAD_LEFT),
                    'short_description' => "Exquisite {$name} crafted in {$metal} with {$stone}. A perfect blend of tradition and timeless elegance.",
                    'description' => "{$name} is a masterpiece of traditional craftsmanship, handcrafted in {$metal} with certified Kundan stones.",
                    'category_id' => $categories[$type]->id,
                    'product_type' => 'simple',
                    'regular_price' => $compare ?: $price,
                    'sale_price' => $compare ? $price : null,
                    'cost_price' => round($price * 0.72, 2),
                    'tax_rate_id' => $tax->id,
                    'hsn_sac' => '7113',
                    'min_order_qty' => 1,
                    'weight' => $weight,
                    'main_image' => $images[$index % count($images)],
                    'shipping_class_id' => $shipping->id,
                    'estimated_delivery' => '3–5 business days',
                    'cod_available' => true,
                    'return_eligible' => true,
                    'return_days' => 15,
                    'warranty' => 'Quality-checked finish',
                    'badge' => $badge,
                    'metal' => $metal,
                    'purity' => $purity,
                    'stone' => $stone,
                    'style' => 'Traditional',
                    'occasion' => str_contains($name, 'Bridal') ? 'Bridal, Festive' : 'Festive, Wedding',
                    'certification' => 'Quality-checked finish',
                    'dimensions_text' => $type === 'earrings' || $type === 'jhumkas' ? 'Length 4.2 cm' : null,
                    'tax_note' => 'Inclusive of all taxes',
                    'highlights' => [
                        'Handcrafted fashion jewellery with a premium anti-tarnish finish',
                        "Studded with authentic {$stone}",
                        'Comes with Geetanjali Jewellers authenticity certificate',
                        'Perfect for bridal and festive occasions',
                        'Premium anti-tarnish plating for lasting shine',
                    ],
                    'is_featured' => $bestseller,
                    'is_new_arrival' => $badge === 'NEW',
                    'is_bestseller' => $bestseller,
                    'is_active' => true,
                    'is_archived' => false,
                    'avg_rating' => $rating,
                    'review_count' => $reviews,
                    'sold_count' => $sold,
                    'published_at' => now()->subDays(max(1, 40 - $index)),
            ];

            if ($product) {
                $product->update($payload + ['slug' => $slug]);
            } else {
                $product = Product::create($payload + ['sku' => $sku, 'slug' => $slug]);
            }

            $pageIds = [$kundan->id => ['sort_order' => $index]];
            if ($type === 'sets' || str_contains($name, 'Bridal')) {
                $pageIds[$bridal->id] = ['sort_order' => $index];
            }
            if (str_contains(strtoupper((string) $badge), 'NEW')) {
                $pageIds[$newArrivals->id] = ['sort_order' => $index];
            }
            $product->collections()->syncWithoutDetaching($pageIds);

            Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'warehouse_id' => $warehouse->id,
                    'batch_lot_number' => null,
                ],
                [
                    'current_stock' => 8,
                    'available_stock' => 8,
                    'reserved_stock' => 0,
                    'reorder_level' => 2,
                    'unit_cost' => $product->cost_price,
                ]
            );
        }

        $featured = Product::where('slug', 'kundan-emerald-drop-earrings')->first();
        if ($featured) {
            $featured->update([
                'gallery_images' => [
                    'public/assets/images/products/gallery/main.jpg',
                    'public/assets/images/products/gallery/closeup.jpg',
                    'public/assets/images/products/gallery/detail.jpg',
                    'public/assets/images/products/gallery/packaging.jpg',
                ],
                'short_description' => 'Exquisite kundan earrings crafted in 22K gold with emerald drops and hand-set stones. A perfect blend of tradition and timeless elegance.',
                'description' => 'These Kundan Emerald Drop Earrings are a masterpiece of traditional craftsmanship, handcrafted in 22K yellow gold with certified Kundan stones and vibrant emerald drops.',
                'highlights' => [
                    'Handcrafted fashion jewellery with a premium anti-tarnish finish',
                    'Studded with authentic Kundan stones and Emerald drops',
                    'Secure screw-back closure for added comfort',
                    'Comes with Geetanjali Jewellers authenticity certificate',
                    'Perfect for bridal and festive occasions',
                ],
            ]);

            foreach ([
                ['Priya Sharma', 5, 'Absolutely stunning earrings. The emerald drops catch the light beautifully and the finish feels truly premium.'],
                ['Ananya Mehta', 5, 'Bought these for my sister’s wedding. Craftsmanship and packaging were excellent.'],
                ['Neha Kapoor', 4, 'Elegant traditional design. Comfortable to wear for long festive evenings.'],
            ] as $review) {
                Review::firstOrCreate(
                    ['product_id' => $featured->id, 'customer_name' => $review[0]],
                    [
                        'rating' => $review[1],
                        'comment' => $review[2],
                        'status' => 'approved',
                        'is_verified_purchase' => true,
                    ]
                );
            }
        }
    }
}
