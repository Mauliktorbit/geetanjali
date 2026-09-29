<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTargetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_coupon_form_has_where_used_and_sale_exclusion_fields(): void
    {
        Category::query()->create([
            'name' => 'Earrings',
            'slug' => 'earrings-coupon-form',
            'is_active' => true,
        ]);
        Product::query()->create([
            'name' => 'Pearl Studs',
            'slug' => 'pearl-studs-coupon-form',
            'sku' => 'EAR-COUPON-FORM',
            'regular_price' => 8000,
            'is_active' => true,
            'is_archived' => false,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/coupons/create')
            ->assertOk()
            ->assertSee('Where this coupon can be used', false)
            ->assertSee('name="included_categories[]"', false)
            ->assertSee('name="included_collections[]"', false)
            ->assertSee('name="included_products[]"', false)
            ->assertSee('already discounted', false);
    }

    public function test_admin_can_save_coupon_categories_collections_products_and_sale_exclusion(): void
    {
        $category = Category::query()->create([
            'name' => 'Rings',
            'slug' => 'rings-coupon-target',
            'is_active' => true,
        ]);
        $collection = Collection::query()->create([
            'name' => 'Bridal',
            'slug' => 'bridal-coupon-target',
            'type' => 'custom',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'name' => 'Gold Ring',
            'slug' => 'gold-ring-coupon-target',
            'sku' => 'RING-COUPON-1',
            'regular_price' => 15000,
            'is_active' => true,
            'is_archived' => false,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/coupons', [
                'code' => 'BRIDAL10',
                'name' => 'Bridal 10% off',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'included_categories' => [$category->id],
                'included_collections' => [$collection->id],
                'included_products' => [$product->id],
                'exclude_sale_items' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::query()->where('code', 'BRIDAL10')->first();
        $this->assertNotNull($coupon);
        $this->assertSame([$category->id], $coupon->includedCategoryIds());
        $this->assertSame([$collection->id], $coupon->includedCollectionIds());
        $this->assertSame([$product->id], $coupon->includedProductIds());
        $this->assertTrue($coupon->exclude_sale_items);
    }

    public function test_coupon_discount_applies_only_to_selected_category_items(): void
    {
        $rings = Category::query()->create([
            'name' => 'Rings',
            'slug' => 'rings-eligible',
            'is_active' => true,
        ]);
        $earrings = Category::query()->create([
            'name' => 'Earrings',
            'slug' => 'earrings-other',
            'is_active' => true,
        ]);

        Coupon::query()->create([
            'code' => 'RINGS10',
            'name' => 'Rings 10% off',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
            'usage_count' => 0,
            'per_customer_limit' => 0,
            'is_stackable' => false,
            'included_categories' => [$rings->id],
        ]);

        $result = app(CouponService::class)->validate('RINGS10', [
            'subtotal' => 30000,
            'product_ids' => [1, 2],
            'category_ids' => [$rings->id, $earrings->id],
            'items' => [
                [
                    'id' => 1,
                    'category_id' => $rings->id,
                    'collection_ids' => [],
                    'price' => 10000,
                    'line_total' => 10000,
                    'on_sale' => false,
                ],
                [
                    'id' => 2,
                    'category_id' => $earrings->id,
                    'collection_ids' => [],
                    'price' => 20000,
                    'line_total' => 20000,
                    'on_sale' => false,
                ],
            ],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertEquals(1000.0, $result['discount']);
    }

    public function test_coupon_applies_to_selected_collection_or_product(): void
    {
        $collection = Collection::query()->create([
            'name' => 'Kundan',
            'slug' => 'kundan-coupon-scope',
            'type' => 'kundan',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'name' => 'Featured Necklace',
            'slug' => 'featured-necklace-coupon',
            'sku' => 'NCK-COUPON-1',
            'regular_price' => 18000,
            'is_active' => true,
            'is_archived' => false,
        ]);

        Coupon::query()->create([
            'code' => 'SCOPE15',
            'name' => 'Scoped 15% off',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'is_active' => true,
            'usage_count' => 0,
            'per_customer_limit' => 0,
            'is_stackable' => false,
            'included_collections' => [$collection->id],
            'included_products' => [$product->id],
        ]);

        $result = app(CouponService::class)->validate('SCOPE15', [
            'subtotal' => 28000,
            'items' => [
                [
                    'id' => $product->id,
                    'category_id' => null,
                    'collection_ids' => [],
                    'price' => 18000,
                    'line_total' => 18000,
                    'on_sale' => false,
                ],
                [
                    'id' => 99,
                    'category_id' => null,
                    'collection_ids' => [$collection->id],
                    'price' => 10000,
                    'line_total' => 10000,
                    'on_sale' => false,
                ],
            ],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertEquals(4200.0, $result['discount']);
    }

    public function test_coupon_skips_already_discounted_products(): void
    {
        Coupon::query()->create([
            'code' => 'NOSALE10',
            'name' => 'No sale stacking',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
            'usage_count' => 0,
            'per_customer_limit' => 0,
            'is_stackable' => false,
            'exclude_sale_items' => true,
        ]);

        $mixed = app(CouponService::class)->validate('NOSALE10', [
            'subtotal' => 30000,
            'items' => [
                [
                    'id' => 1,
                    'price' => 10000,
                    'compare_at_price' => 12000,
                    'line_total' => 10000,
                    'on_sale' => true,
                    'collection_ids' => [],
                ],
                [
                    'id' => 2,
                    'price' => 20000,
                    'line_total' => 20000,
                    'on_sale' => false,
                    'collection_ids' => [],
                ],
            ],
        ]);

        $this->assertTrue($mixed['valid']);
        $this->assertEquals(2000.0, $mixed['discount']);

        $onlySale = app(CouponService::class)->validate('NOSALE10', [
            'subtotal' => 10000,
            'items' => [
                [
                    'id' => 1,
                    'price' => 10000,
                    'compare_at_price' => 12000,
                    'line_total' => 10000,
                    'on_sale' => true,
                    'collection_ids' => [],
                ],
            ],
        ]);

        $this->assertFalse($onlySale['valid']);
        $this->assertStringContainsString('already discounted', strtolower($onlySale['message']));
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }
}
