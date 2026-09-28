<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryListingOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_newest_category_appears_first_on_the_listing(): void
    {
        $admin = $this->admin();

        Category::query()->create([
            'name' => 'Older Necklaces',
            'slug' => 'older-necklaces',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/categories', ['name' => 'Brand New Earrings'])
            ->assertRedirect(route('admin.categories.index'));

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->assertSeeInOrder(['Brand New Earrings', 'Older Necklaces']);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        $admin = $this->admin();

        Category::query()->create([
            'name' => 'Earrings',
            'slug' => 'earrings',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from('/admin/categories/create')
            ->post('/admin/categories', ['name' => 'Earrings'])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors(['name' => 'A category with this name already exists.']);

        $this->actingAs($admin)
            ->from('/admin/categories/create')
            ->post('/admin/categories', ['name' => 'earrings'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Category::query()->whereRaw('LOWER(name) = ?', ['earrings'])->count());
    }

    public function test_category_can_keep_its_own_name_on_update(): void
    {
        $admin = $this->admin();
        $category = Category::query()->create([
            'name' => 'Rings',
            'slug' => 'rings',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put('/admin/categories/'.$category->id, ['name' => 'Rings'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_staff' => true,
            'is_active' => true,
        ]);
    }
}
