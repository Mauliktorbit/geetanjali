<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailMaterialLabelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_material_uses_the_same_label_markup_as_other_product_details(): void
    {
        $warehouse = Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Emerald Drop Earrings',
            'slug' => 'emerald-drop-earrings-pdp',
            'sku' => 'EDE-PDP-1',
            'regular_price' => 12000,
            'metal' => 'Gold-plated',
            'stone' => 'Emerald',
            'style' => 'Traditional',
            'weight' => 12.4,
            'is_active' => true,
            'is_archived' => false,
        ]);

        Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 5,
            'available_stock' => 5,
            'reserved_stock' => 0,
        ]);

        $html = $this->get('/product/'.$product->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('attr-label-top', $html);
        $this->assertStringNotContainsString('metal-pill', $html);
        $this->assertMatchesRegularExpression(
            '/product-attrs[\s\S]*class="label">Material:[\s\S]*class="label">Stone:[\s\S]*class="label">Style:[\s\S]*class="label">Weight:/',
            $html
        );
    }
}
