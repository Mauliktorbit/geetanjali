<?php

namespace Tests\Feature;

use App\Services\StorefrontCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BridalPromoContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
        StorefrontCatalogService::ensurePageCollections();
    }

    public function test_bridal_collection_does_not_promote_a_showroom(): void
    {
        $html = $this->get('/bridal-collection')->assertOk()->getContent();

        $this->assertStringNotContainsString('Showroom', $html);
        $this->assertStringNotContainsString('Find the Store', $html);
        $this->assertStringContainsString('Handcrafted', $html);
        $this->assertStringContainsString('Contact Us', $html);
        $this->assertStringContainsString(route('contact'), $html);
    }
}
