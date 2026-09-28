<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\ShippingMethodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShippingRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_admin_can_open_shipping_rules_and_defaults_are_created(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/shipping-methods')
            ->assertOk()
            ->assertSee('Shipping rules', false)
            ->assertSee('Standard Delivery', false)
            ->assertSee('Express Delivery', false);

        $this->assertSame(2, ShippingMethod::query()->count());
    }

    public function test_admin_can_add_a_shipping_rule(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/shipping-methods', [
                'name' => 'Same day',
                'rate' => 299,
                'estimated_delivery' => 'Same day',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.shipping-methods.index'));

        $this->assertDatabaseHas('shipping_methods', [
            'name' => 'Same day',
            'rate' => 299,
            'is_active' => 1,
        ]);
    }

    public function test_checkout_uses_active_shipping_rules(): void
    {
        $service = app(ShippingMethodService::class);
        $service->ensureDefaults();
        ShippingMethod::query()->where('code', 'express')->update(['rate' => 149]);

        $options = $service->checkoutOptions(500);

        $this->assertArrayHasKey('standard', $options);
        $this->assertArrayHasKey('express', $options);
        $this->assertSame(0.0, $options['standard']['charge']);
        $this->assertSame(149.0, $options['express']['charge']);
    }
}
