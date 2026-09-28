<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventorySortTest extends TestCase
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

    /**
     * @return list<string>
     */
    protected function namesOnPage(string $html): array
    {
        preg_match_all('/data-inventory-name[^>]*>([^<]+)/', $html, $matches);

        return array_values(array_filter($matches[1] ?? [], fn ($name) => $name !== ''));
    }

    public function test_inventory_sorts_by_name_stock_and_status(): void
    {
        $admin = $this->staffUser();
        $this->product('Alpha Earrings', 'AE-1', 20);
        $this->product('Beta Ring', 'BR-1', 3);
        $this->product('Zeta Necklace', 'ZN-1', 0);

        $nameAsc = $this->actingAs($admin)->get('/admin/inventory?sort=name_asc')->assertOk();
        $this->assertSame(['Alpha Earrings', 'Beta Ring', 'Zeta Necklace'], $this->namesOnPage($nameAsc->getContent()));

        $nameDesc = $this->actingAs($admin)->get('/admin/inventory?sort=name_desc')->assertOk();
        $this->assertSame(['Zeta Necklace', 'Beta Ring', 'Alpha Earrings'], $this->namesOnPage($nameDesc->getContent()));

        $stockAsc = $this->actingAs($admin)->get('/admin/inventory?sort=stock_asc')->assertOk();
        $this->assertSame(['Zeta Necklace', 'Beta Ring', 'Alpha Earrings'], $this->namesOnPage($stockAsc->getContent()));

        $stockDesc = $this->actingAs($admin)->get('/admin/inventory?sort=stock_desc')->assertOk();
        $this->assertSame(['Alpha Earrings', 'Beta Ring', 'Zeta Necklace'], $this->namesOnPage($stockDesc->getContent()));

        $statusIn = $this->actingAs($admin)->get('/admin/inventory?sort=status_in')->assertOk();
        $this->assertSame(['Alpha Earrings', 'Beta Ring', 'Zeta Necklace'], $this->namesOnPage($statusIn->getContent()));

        $statusOut = $this->actingAs($admin)->get('/admin/inventory?sort=status_out')->assertOk();
        $this->assertSame(['Zeta Necklace', 'Beta Ring', 'Alpha Earrings'], $this->namesOnPage($statusOut->getContent()));
    }

    public function test_sort_works_with_search_and_stock_filter(): void
    {
        $admin = $this->staffUser();
        $this->product('Alpha Earrings', 'AE-1', 20);
        $this->product('Beta Ring', 'BR-1', 3);
        $this->product('Ruby Ring', 'RR-1', 12);

        $search = $this->actingAs($admin)
            ->get('/admin/inventory?search=Ruby&sort=name_desc')
            ->assertOk();
        $this->assertSame(['Ruby Ring'], $this->namesOnPage($search->getContent()));

        $filtered = $this->actingAs($admin)
            ->get('/admin/inventory?status=in&sort=name_asc')
            ->assertOk();
        $this->assertSame(['Alpha Earrings', 'Ruby Ring'], $this->namesOnPage($filtered->getContent()));
    }
}
