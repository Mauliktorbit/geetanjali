<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_coupon_form_has_date_pickers_max_discount_and_customer_type(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/admin/coupons/create')
            ->assertOk()
            ->assertSee('Maximum discount', false)
            ->assertSee('Who can use this coupon', false)
            ->assertSee('New customers (first order)', false)
            ->assertSee('Returning customers (already ordered)', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/id="coupon-starts"[^>]*type="date"/', $html);
        $this->assertMatchesRegularExpression('/id="coupon-ends"[^>]*type="date"/', $html);
        $this->assertStringContainsString('name="maximum_discount"', $html);
        $this->assertStringContainsString('name="customer_audience"', $html);
    }

    public function test_admin_can_save_dates_maximum_discount_and_customer_audience(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/coupons', [
                'code' => 'LOYAL15',
                'name' => 'Returning 15% off',
                'discount_type' => 'percent',
                'discount_value' => 15,
                'starts_at' => '2026-09-29',
                'ends_at' => '2026-12-31',
                'maximum_discount' => 2000,
                'customer_audience' => 'returning',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::query()->where('code', 'LOYAL15')->first();
        $this->assertNotNull($coupon);
        $this->assertSame('2026-09-29', $coupon->starts_at?->format('Y-m-d'));
        $this->assertSame('2026-12-31', $coupon->ends_at?->format('Y-m-d'));
        $this->assertEquals(2000.0, (float) $coupon->maximum_discount);
        $this->assertFalse($coupon->new_customers_only);
        $this->assertTrue($coupon->existing_customers_only);
        $this->assertSame('returning', $coupon->customerAudience());
    }

    public function test_maximum_discount_caps_the_percent_off(): void
    {
        Coupon::query()->create([
            'code' => 'CAP10',
            'name' => 'Ten percent capped',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'maximum_discount' => 2000,
            'is_active' => true,
            'usage_count' => 0,
            'per_customer_limit' => 0,
            'is_stackable' => false,
        ]);

        $result = app(CouponService::class)->validate('CAP10', [
            'subtotal' => 50000,
        ]);

        $this->assertTrue($result['valid']);
        $this->assertEquals(2000.0, $result['discount']);
    }

    public function test_returning_customer_coupon_rejects_first_time_users(): void
    {
        Coupon::query()->create([
            'code' => 'LOYAL10',
            'name' => 'Loyalty 10%',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
            'usage_count' => 0,
            'per_customer_limit' => 0,
            'is_stackable' => false,
            'new_customers_only' => false,
            'existing_customers_only' => true,
        ]);

        $user = User::factory()->create(['is_staff' => false, 'last_login_at' => now()]);
        $customer = Customer::forUser($user);

        $firstTime = app(CouponService::class)->validate('LOYAL10', [
            'subtotal' => 2500,
            'customer_id' => $customer->id,
        ]);

        $this->assertFalse($firstTime['valid']);
        $this->assertStringContainsString('already placed an order', strtolower($firstTime['message']));

        Order::query()->create([
            'order_number' => 'GJRETURN1',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'grand_total' => 1000,
            'currency' => 'INR',
        ]);

        $returning = app(CouponService::class)->validate('LOYAL10', [
            'subtotal' => 2500,
            'customer_id' => $customer->id,
        ]);

        $this->assertTrue($returning['valid']);
        $this->assertEquals(250.0, $returning['discount']);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }
}
