<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $warehouse;

    protected InventoryService $inventory;

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
        $this->inventory = app(InventoryService::class);
    }

    protected function productWithStock(int $stock): Product
    {
        $product = Product::query()->create([
            'name' => 'Gold Ring',
            'slug' => 'gold-ring-'.uniqid(),
            'sku' => 'GR-'.uniqid(),
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

    protected function lowStockCount(int $productId): int
    {
        return AdminNotification::query()
            ->where('type', 'low_stock')
            ->get()
            ->filter(fn (AdminNotification $row) => (int) data_get($row->data, 'product_id') === $productId)
            ->count();
    }

    public function test_low_stock_notification_is_raised_when_stock_falls_to_the_limit(): void
    {
        User::factory()->create(['is_staff' => true, 'is_active' => true]);
        $product = $this->productWithStock(10);

        $this->inventory->adjustStock($product->id, null, $this->warehouse->id, -5, 'Sale');

        $this->assertSame(5, $this->inventory->availableStockForProduct($product->id));
        $this->assertSame(1, $this->lowStockCount($product->id));
        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'low_stock',
            'title' => 'Low stock alert',
        ]);
    }

    public function test_low_stock_notification_uses_the_defined_threshold(): void
    {
        User::factory()->create(['is_staff' => true, 'is_active' => true]);
        app(SettingService::class)->set('product.low_stock_threshold', 3, 'product', 'integer');
        $product = $this->productWithStock(5);

        $this->inventory->adjustStock($product->id, null, $this->warehouse->id, -1, 'Sale');
        $this->assertSame(0, $this->lowStockCount($product->id));

        $this->inventory->adjustStock($product->id, null, $this->warehouse->id, -1, 'Sale');
        $this->assertSame(1, $this->lowStockCount($product->id));
    }

    public function test_already_low_stock_does_not_create_another_open_alert(): void
    {
        User::factory()->create(['is_staff' => true, 'is_active' => true]);
        $product = $this->productWithStock(6);

        $this->inventory->reserveStock($product->id, null, $this->warehouse->id, 2, 'Order A');
        $this->inventory->reserveStock($product->id, null, $this->warehouse->id, 2, 'Order B');

        $this->assertSame(2, $this->inventory->availableStockForProduct($product->id));
        $this->assertSame(1, $this->lowStockCount($product->id));
    }
}
