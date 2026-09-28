<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryBulkStockTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');

        $this->warehouse = Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    protected function staffUser(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }

    protected function product(string $name, string $sku, int $stock): Product
    {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'sku' => $sku,
            'regular_price' => 1000,
            'is_active' => true,
        ]);

        Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'current_stock' => $stock,
            'available_stock' => $stock,
            'reserved_stock' => 0,
        ]);

        return $product;
    }

    protected function stockFor(Product $product): int
    {
        return (int) Inventory::query()->where('product_id', $product->id)->sum('available_stock');
    }

    public function test_bulk_page_shows_add_and_total_columns(): void
    {
        $admin = $this->staffUser();
        $this->product('Gold Ring', 'GR-1', 10);

        $this->actingAs($admin)
            ->get('/admin/inventory/bulk')
            ->assertOk()
            ->assertSee('Bulk Update Stock', false)
            ->assertSee('Stock to Add', false)
            ->assertSee('New Total Stock', false)
            ->assertSee('Fill all rows', false);
    }

    public function test_selected_products_receive_the_same_added_stock(): void
    {
        $admin = $this->staffUser();
        $ring = $this->product('Gold Ring', 'GR-1', 10);
        $set = $this->product('Pearl Set', 'PS-1', 4);

        $this->actingAs($admin)
            ->post('/admin/inventory/bulk-selected', [
                'ids' => [$ring->id, $set->id],
                'stock_to_add' => 20,
            ])
            ->assertRedirect('/admin/inventory');

        $this->assertSame(30, $this->stockFor($ring));
        $this->assertSame(24, $this->stockFor($set));
    }

    public function test_bulk_form_can_add_different_amounts_per_product(): void
    {
        $admin = $this->staffUser();
        $ring = $this->product('Gold Ring', 'GR-1', 10);
        $set = $this->product('Pearl Set', 'PS-1', 4);

        $this->actingAs($admin)
            ->post('/admin/inventory/bulk', [
                'items' => [
                    ['product_id' => $ring->id, 'stock_to_add' => 5],
                    ['product_id' => $set->id, 'stock_to_add' => ''],
                ],
            ])
            ->assertRedirect('/admin/inventory/bulk');

        $this->assertSame(15, $this->stockFor($ring));
        $this->assertSame(4, $this->stockFor($set));
    }
}
