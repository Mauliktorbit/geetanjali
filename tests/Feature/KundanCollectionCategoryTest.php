<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\StorefrontCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KundanCollectionCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
        StorefrontCatalogService::ensurePageCollections();
    }

    public function test_kundan_page_does_not_render_side_oval_decorations(): void
    {
        $html = $this->get('/kundan')->assertOk()->getContent();

        $this->assertStringNotContainsString('kundan-intro__floral', $html);
        $this->assertStringNotContainsString('kundan-why__floral', $html);
    }

    public function test_kundan_list_view_keeps_compact_card_actions(): void
    {
        $kundan = $this->kundanCollection();
        $this->product('Emerald Drop Earrings', 'emerald-drop-list', 'EDE-LIST-1', null, $kundan);

        $html = $this->get('/kundan?view=list')->assertOk()->getContent();

        $this->assertStringContainsString('bridal-grid--list', $html);
        $this->assertStringContainsString('product-card__actions', $html);
        $this->assertStringContainsString('Emerald Drop Earrings', $html);
    }

    public function test_kundan_does_not_show_sets_when_admin_has_no_sets_category(): void
    {
        $earrings = $this->category('Earrings', 'earrings');
        $kundan = $this->kundanCollection();

        $this->product('Emerald Drop Earrings', 'emerald-drop-earrings', 'EDE-1', $earrings, $kundan);
        $this->product('Kundan Bridal Set', 'kundan-bridal-set', 'KBS-1', null, $kundan);

        $html = $this->get('/kundan')->assertOk()->getContent();

        $this->assertStringNotContainsString('value="sets"', $html);
        $this->assertDoesNotMatchRegularExpression('/kundan-cat-nav__label">\s*Sets\s*</', $html);
        $this->assertStringContainsString('Emerald Drop Earrings', $html);
        $this->assertStringContainsString('Kundan Bridal Set', $html);
    }

    public function test_kundan_sets_link_does_not_list_every_product_when_sets_is_not_an_admin_category(): void
    {
        $earrings = $this->category('Earrings', 'earrings');
        $kundan = $this->kundanCollection();

        $this->product('Emerald Drop Earrings', 'emerald-drop-earrings', 'EDE-1', $earrings, $kundan);
        $this->product('Kundan Bridal Set', 'kundan-bridal-set', 'KBS-1', null, $kundan);

        $html = $this->get('/kundan?category=sets')->assertOk()->getContent();

        $this->assertStringNotContainsString('Emerald Drop Earrings', $html);
        $this->assertStringContainsString('Kundan Bridal Set', $html);
    }

    public function test_category_without_kundan_products_is_hidden_on_kundan(): void
    {
        $orphan = $this->category('Orphan Type', 'orphan-type');
        $earrings = $this->category('Earrings', 'earrings');
        $kundan = $this->kundanCollection();

        $this->product('Emerald Drop Earrings', 'emerald-drop-earrings', 'EDE-NAV-1', $earrings, $kundan);

        $html = $this->get('/kundan')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/kundan-cat-nav__label">\s*Orphan Type\s*</', $html);
        $this->assertStringNotContainsString('value="orphan-type"', $html);
        $this->assertStringContainsString('Earrings', $html);
        $this->assertSame('Orphan Type', $orphan->name);
    }

    public function test_kundan_sets_filter_works_when_admin_has_sets_category(): void
    {
        $earrings = $this->category('Earrings', 'earrings');
        $sets = $this->category('Sets', 'sets');
        $kundan = $this->kundanCollection();

        $this->product('Emerald Drop Earrings', 'emerald-drop-earrings', 'EDE-1', $earrings, $kundan);
        $this->product('Kundan Bridal Set', 'kundan-bridal-set', 'KBS-1', $sets, $kundan);

        $html = $this->get('/kundan?category=sets')->assertOk()->getContent();

        $this->assertStringContainsString('value="sets"', $html);
        $this->assertStringContainsString('Kundan Bridal Set', $html);
        $this->assertStringNotContainsString('Emerald Drop Earrings', $html);
    }

    private function kundanCollection()
    {
        return StorefrontCatalogService::ensurePageCollections()->firstWhere('slug', 'kundan');
    }

    private function category(string $name, string $slug): Category
    {
        return Category::query()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
            'display_order' => 1,
        ]);
    }

    private function product(
        string $name,
        string $slug,
        string $sku,
        ?Category $category,
        $collection
    ): Product {
        $product = Product::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'regular_price' => 12000,
            'is_active' => true,
            'is_archived' => false,
            'category_id' => $category?->id,
        ]);

        $product->collections()->attach($collection->id);

        return $product;
    }
}
