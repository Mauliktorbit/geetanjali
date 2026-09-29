<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_coupon_form_has_usage_and_first_order_fields(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/coupons/create')
            ->assertOk()
            ->assertSee('Total uses', false)
            ->assertSee('Uses per customer', false)
            ->assertSee('Who can use this coupon', false)
            ->assertSee('New customers (first order)', false);
    }

    public function test_admin_can_save_coupon_usage_limits_and_first_order_flag(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/coupons', [
                'code' => 'WELCOME10',
                'name' => 'Welcome 10% off',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'usage_limit' => 100,
                'per_customer_limit' => 1,
                'new_customers_only' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'code' => 'WELCOME10',
            'usage_limit' => 100,
            'per_customer_limit' => 1,
            'new_customers_only' => 1,
        ]);
    }

    public function test_first_order_coupon_is_rejected_after_a_customer_has_ordered(): void
    {
        $coupon = Coupon::query()->create([
            'code' => 'WELCOME10',
            'name' => 'Welcome 10% off',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
            'usage_count' => 0,
            'new_customers_only' => true,
            'per_customer_limit' => 1,
            'is_stackable' => false,
        ]);

        $user = User::factory()->create(['is_staff' => false, 'last_login_at' => now()]);
        $customer = Customer::forUser($user);
        Order::query()->create([
            'order_number' => 'GJCOUPON1',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'grand_total' => 1000,
            'currency' => 'INR',
        ]);

        $result = app(CouponService::class)->validate('WELCOME10', [
            'subtotal' => 2500,
            'customer_id' => $customer->id,
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('first order', strtolower($result['message']));
        $this->assertSame($coupon->code, 'WELCOME10');
    }

    public function test_per_customer_limit_blocks_a_second_use(): void
    {
        $coupon = Coupon::query()->create([
            'code' => 'REPEAT5',
            'name' => 'Five percent',
            'discount_type' => 'percent',
            'discount_value' => 5,
            'is_active' => true,
            'usage_count' => 1,
            'new_customers_only' => false,
            'per_customer_limit' => 1,
            'is_stackable' => false,
        ]);

        $user = User::factory()->create(['is_staff' => false, 'last_login_at' => now()]);
        $customer = Customer::forUser($user);
        Order::query()->create([
            'order_number' => 'GJCOUPON2',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'grand_total' => 1000,
            'currency' => 'INR',
        ]);

        $result = app(CouponService::class)->validate('REPEAT5', [
            'subtotal' => 2500,
            'customer_id' => $customer->id,
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('maximum number of times', $result['message']);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }
}
