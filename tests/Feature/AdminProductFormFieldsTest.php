<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_add_product_page_shows_only_needed_fields(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/products/create')
            ->assertOk()
            ->assertSee('Main photo', false)
            ->assertSee('Product name', false)
            ->assertSee('Short description', false)
            ->assertSee('Jewellery details', false)
            ->assertSee('Regular price', false)
            ->assertSee('Quantity', false)
            ->assertSee('Publish on website', false)
            ->assertSee('Category', false)
            ->assertSee('Collections', false)
            ->assertDontSee('Barcode', false)
            ->assertDontSee('Cost price', false)
            ->assertDontSee('HSN / SAC', false)
            ->assertDontSee('Tax rate', false)
            ->assertDontSee('Min order qty', false)
            ->assertDontSee('Purity', false)
            ->assertDontSee('Shipping class', false)
            ->assertDontSee('SEO title', false)
            ->assertDontSee('Related products', false)
            ->assertDontSee('Frequently bought together', false)
            ->assertDontSee('Subcategory', false)
            ->assertDontSee('Sold count', false);
    }
}
