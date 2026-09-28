<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardAccuracyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
        Carbon::setTestNow(Carbon::parse('2026-09-25 10:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_overview_excludes_refunded_orders_and_matches_admin_lists(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);

        $lowProduct = $this->product('Emerald Jhumka', 'emerald-jhumka', 'EJ-1', 3, $warehouse, 999);
        $outProduct = $this->product('Pearl Tikka', 'pearl-tikka', 'PT-1', 0, $warehouse, 0);
        $this->product('Kundan Set', 'kundan-set', 'KS-1', 20, $warehouse, 192);
        $soldProduct = $this->product('Meenakari Set', 'meenakari-set', 'MS-1', 12, $warehouse, 1);

        Customer::query()->create(['name' => 'Anita', 'email' => 'anita@example.com']);
        Customer::query()->create(['name' => 'Ravi', 'email' => 'ravi@example.com']);

        $this->order('GJ-TODAY', OrderStatus::DELIVERED, 'paid', 5000, now(), $soldProduct, 2);
        $this->order('GJ-OPEN', OrderStatus::CONFIRMED, 'unpaid', 2000, now()->subDays(3), $lowProduct, 1);
        $this->order('GJ-REFUND', OrderStatus::REFUNDED, 'paid', 298500, now()->subDays(5), $lowProduct, 8);
        $this->order('GJ-CANCEL', OrderStatus::CANCELLED, 'unpaid', 10000, now()->subDays(2), $outProduct, 3);
        $this->order('GJ-OLD', OrderStatus::DELIVERED, 'paid', 1000, now()->subMonth(), $soldProduct, 1);

        $overview = app(DashboardService::class)->overview();

        $this->assertSame(5000.0, $overview['sales_today']);
        $this->assertSame(1, $overview['orders_today']);
        $this->assertSame(7000.0, $overview['sales_month']);
        $this->assertSame(2, $overview['orders_month']);
        $this->assertSame(3500.0, $overview['aov']);
        $this->assertSame(1, $overview['pending_orders']);
        $this->assertSame(2, $overview['customers']);
        $this->assertSame(1, $overview['low_stock']);
        $this->assertSame(1, $overview['out_of_stock']);
        $this->assertSame('Meenakari Set', $overview['best_sellers']->first()->product_name);
        $this->assertSame(3, (int) $overview['best_sellers']->first()->qty_sold);
        $this->assertSame(1, (int) $overview['best_sellers']->firstWhere('product_name', 'Emerald Jhumka')->qty_sold);
        $this->assertSame('Emerald Jhumka', $overview['low_stock_items']->first()->name);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('₹5,000.00', false)
            ->assertSee('₹7,000.00', false)
            ->assertSee('Emerald Jhumka', false)
            ->assertSee('Meenakari Set', false)
            ->assertDontSee('Kundan Set', false);

        $this->actingAs($admin)
            ->get('/admin/orders?status=pending')
            ->assertOk()
            ->assertSee('GJ-OPEN', false)
            ->assertDontSee('GJ-TODAY', false)
            ->assertDontSee('GJ-REFUND', false);

        $this->actingAs($admin)
            ->get('/admin/inventory?status=low')
            ->assertOk()
            ->assertSee('Emerald Jhumka', false)
            ->assertDontSee('Pearl Tikka', false)
            ->assertDontSee('Kundan Set', false);
    }

    protected function product(string $name, string $slug, string $sku, int $stock, Warehouse $warehouse, int $soldCount): Product
    {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'regular_price' => 1000,
            'is_active' => true,
            'sold_count' => $soldCount,
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

    protected function order(
        string $number,
        string $status,
        string $payment,
        float $total,
        Carbon $at,
        Product $product,
        int $qty
    ): Order {
        $order = Order::query()->create([
            'order_number' => $number,
            'customer_name' => 'Test customer',
            'status' => $status,
            'payment_status' => $payment,
            'subtotal' => $total,
            'grand_total' => $total,
            'paid_amount' => $payment === 'paid' ? $total : 0,
        ]);

        $order->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $qty,
            'unit_price' => $total / $qty,
            'total' => $total,
        ]);

        return $order->fresh();
    }
}
