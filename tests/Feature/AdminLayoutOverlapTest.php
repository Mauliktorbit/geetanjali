<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutOverlapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_admin_shell_keeps_the_topbar_out_of_the_content_scroll(): void
    {
        $css = (string) file_get_contents(public_path('css/admin.css'));
        $this->assertStringContainsString('html.admin-html', $css);
        $this->assertStringContainsString('body.admin-app', $css);
        $this->assertStringContainsString('.admin-content', $css);
        $this->assertDoesNotMatchRegularExpression('/\.topbar\s*\{[^}]*position:\s*sticky/s', $css);
        $this->assertStringContainsString('calc(100vw - var(--sidebar-width)', $css);
        $this->assertStringContainsString('html.admin-html .swal2-container.swal2-top-end', $css);
        $this->assertStringContainsString('left: var(--sidebar-width)', $css);

        $js = (string) file_get_contents(public_path('js/sweet-alerts.js'));
        $this->assertStringContainsString('gj-swal-notice-wrap', $js);
        $this->assertStringContainsString('backdrop: false', $js);

        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
        $user = User::factory()->create(['last_login_at' => now()]);
        $customer = Customer::forUser($user);
        $order = Order::query()->create([
            'order_number' => 'GJLAYOUT1',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'grand_total' => 1000,
            'currency' => 'INR',
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('class="admin-html"', false)
            ->assertSee('class="admin-app"', false)
            ->assertSee('class="admin-breadcrumbs"', false)
            ->assertSee('Dashboard', false)
            ->assertSee('Orders', false);
    }
}
