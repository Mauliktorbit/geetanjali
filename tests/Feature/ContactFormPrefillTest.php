<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormPrefillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_guest_contact_form_leaves_name_and_email_empty(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="contact-name"[^>]*value=""/', $html);
        $this->assertMatchesRegularExpression('/id="contact-email"[^>]*value=""/', $html);
        $this->assertStringNotContainsString('readonly', $html);
    }

    public function test_logged_in_customer_sees_registered_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Anita Shah',
            'email' => 'anita.shah@example.com',
            'is_staff' => false,
        ]);

        $html = $this->actingAs($user)
            ->get('/contact-us')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="Anita Shah"', $html);
        $this->assertStringContainsString('value="anita.shah@example.com"', $html);
        $this->assertSame(2, substr_count($html, 'readonly'));
    }

    public function test_logged_in_customer_cannot_submit_a_different_name_or_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Anita Shah',
            'email' => 'anita.shah@example.com',
            'is_staff' => false,
        ]);

        $this->actingAs($user)
            ->post('/contact-us', [
                'name' => 'Someone Else',
                'email' => 'wrong@example.com',
                'message' => 'Please call me about a bridal set.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enquiries', [
            'name' => 'Anita Shah',
            'email' => 'anita.shah@example.com',
            'message' => 'Please call me about a bridal set.',
        ]);
        $this->assertSame(0, Enquiry::query()->where('email', 'wrong@example.com')->count());
    }
}
