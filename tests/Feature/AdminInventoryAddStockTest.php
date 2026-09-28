<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryAddStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    protected function staffUser(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }

    protected function productWithStock(int $stock): Product
    {
        $product = Product::query()->create([
            'name' => 'Gold Ring',
            'slug' => 'gold-ring',
            'sku' => 'GR-100',
            'regular_price' => 1000,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);

        Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => $stock,
            'available_stock' => $stock,
            'reserved_stock' => 0,
        ]);

        return $product;
    }

    public function test_update_stock_form_shows_add_and_readonly_totals(): void
    {
        $admin = $this->staffUser();
        $product = $this->productWithStock(10);

        $this->actingAs($admin)
            ->get('/admin/inventory/adjust/'.$product->id)
            ->assertOk()
            ->assertSee('Current Stock', false)
            ->assertSee('Stock to Add', false)
            ->assertSee('New Total Stock', false)
            ->assertDontSee('New stock *', false);
    }

    public function test_stock_to_add_is_added_to_current_stock(): void
    {
        $admin = $this->staffUser();
        $product = $this->productWithStock(10);

        $this->actingAs($admin)
            ->post('/admin/inventory/adjust', [
                'product_id' => $product->id,
                'stock_to_add' => 20,
            ])
            ->assertRedirect('/admin/inventory');

        $this->assertSame(30, (int) Inventory::query()->where('product_id', $product->id)->sum('available_stock'));
    }
}
