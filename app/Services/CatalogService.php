<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CatalogService
{
    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $product = Product::query()
            ->with('category')
            ->whereKey($id)
            ->where('is_active', true)
            ->where('is_archived', false)
            ->first();

        if ($product) {
            return $this->fromProduct($product);
        }

        return $this->demo()->firstWhere('id', $id);
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     * @return array<string, mixed>|null
     */
    public function present(int $id, ?array $snapshot = null): ?array
    {
        $fromSnap = $this->normalizeSnapshot($id, $snapshot);
        if ($fromSnap) {
            return $fromSnap;
        }

        return $this->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function resolve(int $id, array $data = []): ?array
    {
        $fromSnap = $this->fromSnapshot($id, $data);
        if ($fromSnap) {
            return $fromSnap;
        }

        return $this->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function fromSnapshot(int $id, array $data): ?array
    {
        return $this->normalizeSnapshot($id, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function fromProduct(Product $product): array
    {
        $price = (float) $product->effective_price;
        $compare = (float) $product->regular_price;
        $discount = ($compare > $price && $compare > 0)
            ? (int) round((1 - ($price / $compare)) * 100).'% OFF'
            : null;

        $image = $product->main_image ?: 'public/assets/images/categories/rings.jpg';
        $weight = $product->weight ? rtrim(rtrim(number_format((float) $product->weight, 3, '.', ''), '0'), '.').' g' : null;

        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $image,
            'price' => $price,
            'compare_at_price' => $compare > $price ? $compare : null,
            'discount_label' => $discount,
            'metal' => $product->category?->name ?: ($product->product_type ? ucfirst((string) $product->product_type) : null),
            'weight' => $weight,
            'url' => route('products.show', $product->slug),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function demo(): Collection
    {
        $url = fn (string $slug) => route('products.show', $slug);

        return collect([
            $this->demoItem(1, 'Gold Floral Pendant Set', 'gold-floral-pendant-set', 'public/assets/images/products/gold-floral-pendant.jpg', 48750, 54200, '10% OFF', '22KT Gold', '18.250 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(2, 'Kundan Jhumka Earrings', 'kundan-jhumka-earrings', 'public/assets/images/products/gold-drop-earrings.jpg', 62400, 69500, '10% OFF', '22KT Gold', '22.100 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(3, 'Diamond Solitaire Ring', 'diamond-solitaire-ring', 'public/assets/images/categories/rings.jpg', 98500, 109000, '10% OFF', '18KT Gold', 'Diamond: 0.50 CT', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(4, 'Classic Gold Bangles', 'classic-gold-bangles', 'public/assets/images/products/classic-gold-bangle.jpg', 158900, 175000, '9% OFF', '22KT Gold', '36.500 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(5, 'Traditional Gold Necklace', 'traditional-gold-necklace', 'public/assets/images/products/traditional-gold-necklace.jpg', 215000, 238000, '10% OFF', '22KT Gold', '42.800 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(6, 'Emerald Drop Earrings', 'emerald-drop-earrings', 'public/assets/images/categories/earrings.jpg', 36800, 42000, '12% OFF', '18KT Gold', '12.400 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(7, 'Pearl Choker Set', 'pearl-choker-set', 'public/assets/images/categories/necklaces.jpg', 72500, 85000, '15% OFF', '22KT Gold', '28.750 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(8, 'Antique Gold Ring', 'antique-gold-ring', 'public/assets/images/categories/diamond.jpg', 28900, 32500, '11% OFF', '22KT Gold', '6.200 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(9, 'Temple Jewellery Set', 'temple-jewellery-set', 'public/assets/images/categories/bridal.jpg', 185000, 210000, '12% OFF', '22KT Gold', '58.300 g', $url('kundan-emerald-drop-earrings')),
            $this->demoItem(10, 'Rose Gold Bracelet', 'rose-gold-bracelet', 'public/assets/images/categories/bangles.jpg', 45600, 52000, '12% OFF', '18KT Rose Gold', '14.800 g', $url('kundan-emerald-drop-earrings')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function demoItem(
        int $id,
        string $name,
        string $slug,
        string $image,
        float $price,
        float $compare,
        string $discount,
        string $metal,
        string $weight,
        string $url,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'image' => $image,
            'price' => $price,
            'compare_at_price' => $compare,
            'discount_label' => $discount,
            'metal' => $metal,
            'weight' => $weight,
            'url' => $url,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     * @return array<string, mixed>|null
     */
    private function normalizeSnapshot(int $id, ?array $snapshot): ?array
    {
        if (! is_array($snapshot) || $id < 1) {
            return null;
        }

        $name = trim((string) ($snapshot['name'] ?? ''));
        $price = (float) ($snapshot['price'] ?? 0);
        if ($name === '' || $price <= 0) {
            return null;
        }

        $compare = isset($snapshot['compare_at_price']) && $snapshot['compare_at_price'] !== ''
            ? (float) $snapshot['compare_at_price']
            : null;

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $snapshot['slug'] ?? null,
            'image' => $snapshot['image'] ?: 'public/assets/images/categories/rings.jpg',
            'price' => $price,
            'compare_at_price' => $compare && $compare > $price ? $compare : null,
            'discount_label' => $snapshot['discount_label'] ?? null,
            'metal' => $snapshot['metal'] ?? null,
            'weight' => $snapshot['weight'] ?? null,
            'url' => $snapshot['url'] ?: route('products.show', $snapshot['slug'] ?? 'kundan-emerald-drop-earrings'),
        ];
    }
}
