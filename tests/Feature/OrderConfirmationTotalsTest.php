<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderConfirmationTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_confirmation_page_shows_subtotal_coupon_discount_and_shipping(): void
    {
        [$customer, $order] = $this->orderWithCouponAndShipping();

        $this->actingAs($customer->user)
            ->get('/order-confirmation/'.$order->order_number)
            ->assertOk()
            ->assertSee('Subtotal', false)
            ->assertSee('₹38,500', false)
            ->assertSee('Discount (TEST30)', false)
            ->assertSee('- ₹11,550', false)
            ->assertSee('Shipping', false)
            ->assertSee('Express Delivery', false)
            ->assertSee('₹199', false)
            ->assertSee('Order Total', false)
            ->assertSee('₹27,149', false);
    }

    public function test_admin_order_page_shows_the_same_total_breakdown(): void
    {
        [, $order] = $this->orderWithCouponAndShipping();

        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Kundan Necklace', false)
            ->assertSeeInOrder([
                'Subtotal',
                'Discount (TEST30)',
                'Shipping (Express Delivery)',
                'Order Total',
            ])
            ->assertSee('₹38,500.00', false)
            ->assertSee('- ₹11,550.00', false)
            ->assertSee('₹199.00', false)
            ->assertSee('₹27,149.00', false)
            ->assertSee('Payment method', false)
            ->assertSee('UPI', false)
            ->assertSee('Shipping method', false)
            ->assertSee('Express Delivery', false)
            ->assertSee('value="confirmed"', false)
            ->assertDontSee('value="new"', false);

        $this->actingAs($admin)
            ->from('/admin/orders/'.$order->id)
            ->post('/admin/orders/'.$order->id.'/status', ['status' => 'new'])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::CONFIRMED, $order->fresh()->status);
    }

    public function test_standard_shipping_is_shown_as_free_instead_of_omitted(): void
    {
        $user = User::factory()->create(['last_login_at' => now()]);
        $customer = Customer::forUser($user);
        $order = $this->makeOrder($customer, [
            'coupon_code' => 'TEST30',
            'subtotal' => 38500,
            'discount_amount' => 11550,
            'shipping_method' => 'standard',
            'shipping_charge' => 0,
            'grand_total' => 26950,
        ]);

        $this->actingAs($user)
            ->get('/order-confirmation/'.$order->order_number)
            ->assertOk()
            ->assertSee('Shipping', false)
            ->assertSee('Free', false)
            ->assertSee('Standard Delivery', false)
            ->assertSee('Discount (TEST30)', false);
    }

    /**
     * @return array{0: Customer, 1: Order}
     */
    private function orderWithCouponAndShipping(): array
    {
        $user = User::factory()->create(['last_login_at' => now()]);
        $customer = Customer::forUser($user);
        $order = $this->makeOrder($customer, [
            'coupon_code' => 'TEST30',
            'subtotal' => 38500,
            'discount_amount' => 11550,
            'shipping_method' => 'express',
            'shipping_charge' => 199,
            'grand_total' => 27149,
        ]);

        return [$customer, $order];
    }

    /**
     * @param  array<string, mixed>  $totals
     */
    private function makeOrder(Customer $customer, array $totals): Order
    {
        $order = Order::query()->create([
            'order_number' => 'GJTEST'.fake()->unique()->numerify('####'),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'shipping_method' => $totals['shipping_method'],
            'coupon_code' => $totals['coupon_code'],
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_amount'],
            'shipping_charge' => $totals['shipping_charge'],
            'grand_total' => $totals['grand_total'],
            'currency' => 'INR',
            'shipping_city' => 'Jaipur',
            'shipping_pincode' => '302001',
            'confirmed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Kundan Necklace',
            'quantity' => 1,
            'unit_price' => $totals['subtotal'],
            'total' => $totals['subtotal'],
        ]);

        return $order->load('items');
    }
}
