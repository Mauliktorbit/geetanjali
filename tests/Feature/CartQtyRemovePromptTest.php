<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartQtyRemovePromptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_cart_page_has_minus_control_and_remove_form_for_a_single_item(): void
    {
        $product = $this->inStockProduct();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->get('/cart')
            ->assertOk()
            ->assertSee('data-qty-minus', false)
            ->assertSee('data-remove-form', false)
            ->assertSee((string) $product->name, false);
    }

    public function test_removing_the_only_cart_item_empties_the_cart(): void
    {
        $product = $this->inStockProduct();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->delete('/cart/remove/'.$product->id)->assertRedirect();

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Your cart is empty', false)
            ->assertDontSee('data-cart-item', false);
    }

    public function test_cart_script_asks_to_remove_when_minus_would_drop_quantity_below_one(): void
    {
        $js = (string) file_get_contents(public_path('assets/js/cart.js'));

        $this->assertStringContainsString('function promptRemoveItem', $js);
        $this->assertStringContainsString('current <= 1', $js);
        $this->assertStringContainsString('Remove this item from your cart?', $js);
    }

    private function inStockProduct(): Product
    {
        $warehouse = Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Emerald Drop Earrings',
            'slug' => 'emerald-drop-earrings-cart',
            'sku' => 'EDE-CART-1',
            'regular_price' => 12000,
            'is_active' => true,
            'is_archived' => false,
        ]);

        Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'current_stock' => 8,
            'available_stock' => 8,
            'reserved_stock' => 0,
        ]);

        return $product;
    }
}
