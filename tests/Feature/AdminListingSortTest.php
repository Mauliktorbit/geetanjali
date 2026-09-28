<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListingSortTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_products_and_orders_sort_from_table_headers(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        Product::query()->create([
            'name' => 'Alpha Jhumka',
            'slug' => 'alpha-jhumka',
            'sku' => 'AJ-1',
            'regular_price' => 500,
            'is_active' => true,
        ]);
        Product::query()->create([
            'name' => 'Zeta Necklace',
            'slug' => 'zeta-necklace',
            'sku' => 'ZN-1',
            'regular_price' => 1500,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/products?sort=name&direction=asc')
            ->assertOk()
            ->assertSee('th-sort', false)
            ->assertSeeInOrder(['Alpha Jhumka', 'Zeta Necklace']);

        $this->actingAs($admin)
            ->get('/admin/products?sort=name&direction=desc')
            ->assertOk()
            ->assertSeeInOrder(['Zeta Necklace', 'Alpha Jhumka']);

        $this->actingAs($admin)
            ->get('/admin/products?sort=price&direction=asc')
            ->assertOk()
            ->assertSeeInOrder(['Alpha Jhumka', 'Zeta Necklace']);

        Order::query()->create([
            'order_number' => 'GJ-LOW',
            'customer_name' => 'Anita',
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'unpaid',
            'grand_total' => 200,
        ]);
        Order::query()->create([
            'order_number' => 'GJ-HIGH',
            'customer_name' => 'Zubin',
            'status' => OrderStatus::DELIVERED,
            'payment_status' => 'paid',
            'grand_total' => 900,
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders?sort=total&direction=asc')
            ->assertOk()
            ->assertSeeInOrder(['GJ-LOW', 'GJ-HIGH']);

        $this->actingAs($admin)
            ->get('/admin/orders?sort=customer&direction=desc')
            ->assertOk()
            ->assertSeeInOrder(['Zubin', 'Anita']);
    }
}
