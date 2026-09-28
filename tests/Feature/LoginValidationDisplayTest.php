<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_invalid_login_shows_the_error_only_once(): void
    {
        $html = $this->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])
            ->assertOk()
            ->getContent();

        $message = 'Invalid email or password.';

        $this->assertSame(1, substr_count($html, $message));
        $this->assertStringContainsString('class="auth-error"', $html);
        $this->assertStringNotContainsString('Please correct the following', $html);
    }

    public function test_auth_script_hides_validation_after_a_few_seconds(): void
    {
        $js = (string) file_get_contents(public_path('assets/js/auth.js'));

        $this->assertStringContainsString('function dismissAuthMessages', $js);
        $this->assertStringContainsString('4000', $js);
    }
}
