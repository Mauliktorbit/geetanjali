<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $variants = $data['variants'] ?? [];
            $tags = $data['tags'] ?? [];
            $related = $data['related_products'] ?? [];
            $fbt = $data['frequently_bought_together'] ?? [];
            $collections = $data['collections'] ?? [];
            $attributeMatrix = $data['attribute_matrix'] ?? [];
            unset($data['variants'], $data['tags'], $data['related_products'], $data['frequently_bought_together'], $data['attribute_matrix'], $data['collections'], $data['highlights_text'], $data['quantity'], $data['stock_status'], $data['keep_gallery'], $data['remove_main_image'], $data['gallery_sync']);

            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
            $data['sku'] = $data['sku'] ?? $this->generateSku($data['name']);

            /** @var Product $product */
            $product = parent::create($data);

            if (! empty($tags)) {
                $product->tags()->sync($tags);
            }

            $product->collections()->sync($this->collectionSync($collections));

            $this->syncRelations($product, $related, 'related');
            $this->syncRelations($product, $fbt, 'fbt');

            if (($data['product_type'] ?? ProductType::SIMPLE) === ProductType::VARIABLE) {
                if (! empty($attributeMatrix)) {
                    $this->generateVariants($product, $attributeMatrix, $variants);
                } elseif (! empty($variants)) {
                    $this->upsertVariants($product, $variants);
                }
            }

            return $product->fresh(['variants', 'tags', 'category', 'brand']);
        });
    }

    public function update(Product|\Illuminate\Database\Eloquent\Model $model, array $data): Product
    {
        return DB::transaction(function () use ($model, $data) {
            /** @var Product $product */
            $product = $model;

            $variants = $data['variants'] ?? null;
            $tags = $data['tags'] ?? null;
            $related = $data['related_products'] ?? null;
            $fbt = $data['frequently_bought_together'] ?? null;
            $collections = $data['collections'] ?? null;
            unset($data['variants'], $data['tags'], $data['related_products'], $data['frequently_bought_together'], $data['attribute_matrix'], $data['collections'], $data['highlights_text'], $data['quantity'], $data['stock_status'], $data['keep_gallery'], $data['remove_main_image'], $data['gallery_sync']);

            if (! empty($data['name']) && empty($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            } elseif (! empty($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['slug'], $product->id);
            }

            parent::update($product, $data);

            if (is_array($tags)) {
                $product->tags()->sync($tags);
            }
            if (is_array($related)) {
                $this->syncRelations($product, $related, 'related');
            }
            if (is_array($fbt)) {
                $this->syncRelations($product, $fbt, 'fbt');
            }
            if (is_array($collections)) {
                $product->collections()->sync($this->collectionSync($collections));
            }
            if (is_array($variants)) {
                $this->upsertVariants($product, $variants);
            }

            return $product->fresh(['variants', 'tags']);
        });
    }

    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product->load(['tags', 'variants.attributeValues', 'relatedProducts', 'frequentlyBoughtTogether', 'collections']);

            $data = $product->replicate([
                'slug', 'sku', 'view_count', 'wishlist_count', 'avg_rating', 'review_count',
            ])->toArray();

            $data['name'] = $product->name . ' (Copy)';
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['sku'] = $this->generateSku($data['name']);
            $data['is_active'] = false;

            $copy = Product::create($data);
            $copy->tags()->sync($product->tags->pluck('id'));

            foreach ($product->variants as $variant) {
                $vData = $variant->replicate(['sku'])->toArray();
                $vData['sku'] = $this->generateSku($copy->name . '-' . ($variant->name ?? 'V'));
                $newVariant = $copy->variants()->create($vData);

                $sync = [];
                foreach ($variant->attributeValues as $av) {
                    $sync[$av->id] = ['attribute_id' => $av->pivot->attribute_id];
                }
                if ($sync) {
                    $newVariant->attributeValues()->sync($sync);
                }
            }

            $copy->collections()->sync($product->collections->pluck('id')->all());
            $this->syncRelations($copy, $product->relatedProducts->pluck('id')->all(), 'related');
            $this->syncRelations($copy, $product->frequentlyBoughtTogether->pluck('id')->all(), 'fbt');

            return $copy->fresh(['variants', 'tags']);
        });
    }

    public function archive(Product $product): Product
    {
        $product->update([
            'is_archived' => true,
            'is_active' => false,
        ]);

        return $product->fresh();
    }

    public function bulkUpdatePrice(array $ids, float $regularPrice, ?float $salePrice = null): int
    {
        $data = ['regular_price' => $regularPrice];
        if ($salePrice !== null) {
            $data['sale_price'] = $salePrice;
        }

        return $this->bulkUpdate($ids, $data);
    }

    public function bulkUpdateCategory(array $ids, int $categoryId, ?int $subcategoryId = null): int
    {
        return $this->bulkUpdate($ids, array_filter([
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
        ], fn ($v) => $v !== null));
    }

    public function bulkUpdateTax(array $ids, int $taxRateId): int
    {
        return $this->bulkUpdate($ids, ['tax_rate_id' => $taxRateId]);
    }

    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return $this->bulkUpdate($ids, ['is_active' => $isActive]);
    }

    public function bulkUpdateStock(array $ids, int $warehouseId, int $stock, InventoryService $inventoryService): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $product = Product::find($id);
            if (! $product) {
                continue;
            }
            $inventory = $product->inventories()->where('warehouse_id', $warehouseId)->first();
            $current = $inventory?->current_stock ?? 0;
            $delta = $stock - $current;
            if ($delta !== 0) {
                $inventoryService->adjustStock(
                    $product->id,
                    null,
                    $warehouseId,
                    $delta,
                    'Bulk stock update'
                );
            }
            $count++;
        }

        return $count;
    }

    public function generateVariants(Product $product, array $attributeValueGroups, array $overrides = []): void
    {
        $combinations = $this->cartesian($attributeValueGroups);
        $overrideMap = collect($overrides)->keyBy(function ($row) {
            return collect($row['attribute_value_ids'] ?? [])->sort()->implode('-');
        });

        foreach ($combinations as $valueIds) {
            $values = AttributeValue::with('attribute')->whereIn('id', $valueIds)->get();
            $key = collect($valueIds)->sort()->implode('-');
            $override = $overrideMap->get($key, []);

            $label = $values->map(fn ($v) => $v->value)->implode(' / ');
            $sku = $override['sku'] ?? $this->generateSku($product->sku . '-' . $label);

            $variant = $product->variants()->create([
                'sku' => $sku,
                'name' => $override['name'] ?? $label,
                'price' => $override['price'] ?? $product->regular_price,
                'sale_price' => $override['sale_price'] ?? $product->sale_price,
                'cost' => $override['cost'] ?? $product->cost_price,
                'stock' => $override['stock'] ?? 0,
                'is_active' => $override['is_active'] ?? true,
                'attribute_data' => $values->mapWithKeys(
                    fn ($v) => [$v->attribute->slug => $v->value]
                )->all(),
            ]);

            $sync = [];
            foreach ($values as $value) {
                $sync[$value->id] = ['attribute_id' => $value->attribute_id];
            }
            $variant->attributeValues()->sync($sync);
        }
    }

    public function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'product';
        $slug = $base;
        $i = 1;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function generateSku(string $name): string
    {
        $base = strtoupper(Str::slug(Str::limit($name, 20, ''), ''));
        $base = $base ?: 'SKU';

        do {
            $sku = $base . '-' . strtoupper(Str::random(4));
        } while (Product::withTrashed()->where('sku', $sku)->exists() || ProductVariant::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    protected function upsertVariants(Product $product, array $variants): void
    {
        $keep = [];

        foreach ($variants as $row) {
            $payload = [
                'sku' => $row['sku'] ?? $this->generateSku($product->name),
                'barcode' => $row['barcode'] ?? null,
                'name' => $row['name'] ?? null,
                'price' => $row['price'] ?? $product->regular_price,
                'sale_price' => $row['sale_price'] ?? null,
                'cost' => $row['cost'] ?? null,
                'stock' => $row['stock'] ?? 0,
                'image' => $row['image'] ?? null,
                'weight' => $row['weight'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'attribute_data' => $row['attribute_data'] ?? null,
            ];

            if (! empty($row['id'])) {
                $variant = $product->variants()->find($row['id']);
                if ($variant) {
                    $variant->update($payload);
                    $keep[] = $variant->id;
                    continue;
                }
            }

            $variant = $product->variants()->create($payload);
            $keep[] = $variant->id;

            if (! empty($row['attribute_value_ids'])) {
                $sync = [];
                $values = AttributeValue::whereIn('id', $row['attribute_value_ids'])->get();
                foreach ($values as $value) {
                    $sync[$value->id] = ['attribute_id' => $value->attribute_id];
                }
                $variant->attributeValues()->sync($sync);
            }
        }

        $product->variants()->whereNotIn('id', $keep)->delete();
    }

    /**
     * @param  list<int|string>  $ids
     * @return array<int, array{sort_order: int}>
     */
    protected function collectionSync(array $ids): array
    {
        $sync = [];
        foreach (array_values(array_unique(array_map('intval', $ids))) as $index => $id) {
            if ($id < 1) {
                continue;
            }
            $sync[$id] = ['sort_order' => $index];
        }

        return $sync;
    }

    protected function syncRelations(Product $product, array $ids, string $type): void
    {
        DB::table('product_related')
            ->where('product_id', $product->id)
            ->where('relation_type', $type)
            ->delete();

        $rows = [];
        foreach (array_unique($ids) as $id) {
            if ((int) $id === (int) $product->id) {
                continue;
            }
            $rows[] = [
                'product_id' => $product->id,
                'related_product_id' => (int) $id,
                'relation_type' => $type,
            ];
        }

        if ($rows) {
            DB::table('product_related')->insert($rows);
        }
    }

    protected function cartesian(array $groups): array
    {
        $groups = array_values(array_filter(array_map('array_values', $groups)));
        if ($groups === []) {
            return [];
        }

        $result = [[]];
        foreach ($groups as $group) {
            $tmp = [];
            foreach ($result as $prefix) {
                foreach ($group as $item) {
                    $tmp[] = array_merge($prefix, [$item]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }
}
