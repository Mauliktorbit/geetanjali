<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository
{
    protected array $searchable = ['name', 'sku', 'barcode', 'slug'];

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        $query->with(['category', 'collections']);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['collection_id'])) {
            $query->whereHas(
                'collections',
                fn (Builder $q) => $q->where('collections.id', $filters['collection_id'])
            );
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        if (isset($filters['is_archived'])) {
            $query->where('is_archived', (bool) $filters['is_archived']);
        }

        if (! empty($filters['low_stock'])) {
            $query->whereHas('inventories', fn (Builder $q) => $q->whereColumn('available_stock', '<=', 'reorder_level'));
        }
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->query()->where('sku', $sku)->first();
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->query()->where('slug', $slug)->first();
    }
}
