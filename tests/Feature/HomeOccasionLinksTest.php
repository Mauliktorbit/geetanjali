<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeOccasionLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_home_occasions_use_unique_suitable_links(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Party', $html);
        $this->assertStringNotContainsString('Engagement', $html);

        preg_match_all('/id="occasions".*?<\\/section>/s', $html, $section);
        $block = $section[0][0] ?? '';
        preg_match_all('/href="([^"]+)"/', $block, $hrefs);
        $urls = array_values(array_unique($hrefs[1] ?? []));

        $this->assertCount(4, $urls);
        $this->assertNotSame(array_unique($urls), []);
        $this->assertCount(4, array_unique($urls), 'Each occasion must open a different page');
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'bridal-collection')));
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'kundan')));
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'new-arrivals')));
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'offers')));
    }
}
