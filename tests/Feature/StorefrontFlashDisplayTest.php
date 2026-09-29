<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFlashDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_success_flash_is_emitted_once_as_toast_payload(): void
    {
        $message = 'Product added to cart.';

        $html = $this->withSession(['success' => $message])
            ->get('/cart')
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, $message));
        $this->assertStringContainsString('id="app-flash-data"', $html);
        $this->assertStringNotContainsString('cart-flash', $html);
        $this->assertStringNotContainsString('Please correct the following', $html);
        $this->assertStringNotContainsString('class="alert alert-success"', $html);
    }

    public function test_account_success_flash_is_emitted_once_as_toast_payload(): void
    {
        $message = 'Account details updated.';
        $user = User::factory()->create(['is_staff' => false]);

        $html = $this->actingAs($user)
            ->withSession(['success' => $message])
            ->get('/my-account/details')
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, $message));
        $this->assertStringContainsString('id="app-flash-data"', $html);
        $this->assertStringNotContainsString('account-flash--success', $html);
        $this->assertStringNotContainsString('class="alert alert-success"', $html);
    }

    public function test_contact_validation_shows_each_field_error_once(): void
    {
        $html = $this->from('/contact-us')
            ->followingRedirects()
            ->post('/contact-us', [])
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Please correct the following', $html);
        $this->assertStringNotContainsString('id="app-flash-data"', $html);
        $this->assertSame(1, substr_count($html, 'The name field is required.'));
        $this->assertSame(1, substr_count($html, 'The email field is required.'));
        $this->assertSame(1, substr_count($html, 'The message field is required.'));
    }

    public function test_scripts_auto_hide_toasts_and_validation(): void
    {
        $alerts = (string) file_get_contents(public_path('js/sweet-alerts.js'));
        $storefront = (string) file_get_contents(public_path('assets/js/storefront.js'));
        $auth = (string) file_get_contents(public_path('assets/js/auth.js'));

        $this->assertStringContainsString('timer: 4000', $alerts);
        $this->assertStringContainsString('backdrop: false', $alerts);
        $this->assertStringContainsString('window.__gjSweetAlertsBound', $alerts);
        $this->assertStringContainsString('function consumeFlashPayload', $alerts);
        $this->assertStringContainsString('function dismissStorefrontMessages', $storefront);
        $this->assertStringContainsString('AppAlert.toast', $storefront);
        $this->assertStringContainsString('4000', $storefront);
        $this->assertStringContainsString('function dismissAuthMessages', $auth);
        $this->assertStringContainsString('4000', $auth);
    }
}
