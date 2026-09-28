<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontBackNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_cart_continue_shopping_uses_storefront_back(): void
    {
        $this->get('/cart')
            ->assertOk()
            ->assertSee('data-storefront-back', false)
            ->assertSee('Continue Shopping', false);
    }

    public function test_storefront_script_remembers_the_last_shop_page(): void
    {
        $js = (string) file_get_contents(public_path('assets/js/storefront.js'));

        $this->assertStringContainsString('geetanjaliLastShopUrl', $js);
        $this->assertStringContainsString('data-storefront-back', $js);
        $this->assertStringContainsString('function storefrontBackUrl', $js);
    }

    public function test_listing_filters_replace_history_instead_of_stacking_it(): void
    {
        $js = (string) file_get_contents(public_path('assets/js/app.js'));

        $this->assertStringContainsString('window.location.replace', $js);
        $this->assertStringContainsString('function submitListingForm', $js);
        $this->assertStringNotContainsString('ensureCollectionPaginationHash', $js);
    }

    public function test_login_json_replaces_history_with_the_intended_page(): void
    {
        $user = User::factory()->create([
            'last_login_at' => now(),
        ]);

        $this->get('/checkout')->assertRedirect();

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect', route('checkout.index'));
    }

    public function test_invalid_json_login_returns_the_error_once(): void
    {
        $this->postJson('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Invalid email or password.');
    }
}
