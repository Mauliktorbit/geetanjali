<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactCompanyDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_contact_page_shows_company_email_and_phone_not_vendor_details(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        $this->assertStringNotContainsString('maulik@torbitmultisoft.com', $html);
        $this->assertStringContainsString('info@geetanjalijewellers.com', $html);
        $this->assertStringContainsString('+91 95839 59503', $html);
        $this->assertStringContainsString('mailto:info@geetanjalijewellers.com', $html);
        $this->assertStringContainsString('tel:+919583959503', $html);
    }

    public function test_contact_page_ignores_placeholder_settings_email(): void
    {
        Setting::query()->create([
            'key' => 'store_email',
            'group' => 'general',
            'value' => 'maulik@torbitmultisoft.com',
            'type' => 'string',
        ]);

        $html = $this->get('/contact-us')->assertOk()->getContent();

        $this->assertStringNotContainsString('maulik@torbitmultisoft.com', $html);
        $this->assertStringContainsString('info@geetanjalijewellers.com', $html);
    }
}
