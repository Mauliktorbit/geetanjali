<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeoSectionsRemovedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_policy_pages_do_not_show_an_seo_section(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        foreach (['shipping', 'terms', 'privacy'] as $policy) {
            $this->actingAs($admin)
                ->get('/admin/policies/'.$policy)
                ->assertOk()
                ->assertSee('Show this page on the website', false)
                ->assertDontSee('SEO title', false)
                ->assertDontSee('SEO description', false)
                ->assertDontSee('SEO & visibility', false)
                ->assertDontSee('policy-seo-title', false);
        }
    }

    public function test_collection_pages_do_not_show_an_seo_section(): void
    {
        $admin = User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);

        $collection = Collection::query()->create([
            'name' => 'Festive Edit',
            'slug' => 'festive-edit-seo-test',
            'type' => 'custom',
            'seo_title' => 'Hidden SEO title',
            'seo_description' => 'Hidden SEO description',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/collections/create')
            ->assertOk()
            ->assertDontSee('SEO title', false)
            ->assertDontSee('SEO description', false);

        $this->actingAs($admin)
            ->get('/admin/collections/'.$collection->id.'/edit')
            ->assertOk()
            ->assertSee('Festive Edit', false)
            ->assertDontSee('SEO title', false)
            ->assertDontSee('SEO description', false);

        $this->actingAs($admin)
            ->get('/admin/collections/'.$collection->id)
            ->assertOk()
            ->assertSee('Festive Edit', false)
            ->assertDontSee('SEO title', false)
            ->assertDontSee('SEO description', false);
    }
}
