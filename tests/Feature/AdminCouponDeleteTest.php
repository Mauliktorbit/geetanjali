<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_coupon_listing_has_a_delete_action(): void
    {
        $admin = $this->admin();
        $coupon = $this->coupon();

        $this->actingAs($admin)
            ->get('/admin/coupons')
            ->assertOk()
            ->assertSee('/admin/coupons/'.$coupon->id, false)
            ->assertSee('Delete coupon '.$coupon->code, false);
    }

    public function test_admin_can_delete_a_coupon(): void
    {
        $admin = $this->admin();
        $coupon = $this->coupon();

        $this->actingAs($admin)
            ->from('/admin/coupons')
            ->delete('/admin/coupons/'.$coupon->id)
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertSoftDeleted('coupons', ['id' => $coupon->id]);

        $this->actingAs($admin)
            ->get('/admin/coupons')
            ->assertOk()
            ->assertDontSee($coupon->code, false);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }

    private function coupon(): Coupon
    {
        return Coupon::query()->create([
            'code' => 'TEST30',
            'name' => 'Test thirty percent',
            'discount_type' => 'percent',
            'discount_value' => 30,
            'is_active' => true,
            'usage_count' => 0,
            'new_customers_only' => false,
            'is_stackable' => false,
        ]);
    }
}
