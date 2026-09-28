<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_dashboard_shows_store_overview_sections(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('This Week', false)
            ->assertSee('This Month', false)
            ->assertSee('Recent orders', false)
            ->assertSee('Best sellers', false)
            ->assertSee('Low stock', false)
            ->assertSee('Quick actions', false)
            ->assertSee('Sales (last 14 days)', false)
            ->assertSee('Nothing waiting', false)
            ->assertSee('Update Stock', false)
            ->assertSee('/admin/inventory/adjust', false)
            ->assertSee('Reports', false)
            ->assertSee('/admin/reports', false);

        $this->actingAs($admin)
            ->get('/admin/inventory/adjust')
            ->assertOk()
            ->assertSee('Stock to Add', false);

        $this->actingAs($admin)
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Reports', false)
            ->assertSee('Revenue by period', false);
    }
}
