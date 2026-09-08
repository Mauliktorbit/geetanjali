<?php

namespace App\Repositories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InventoryRepository extends BaseRepository
{
    protected array $searchable = ['batch_lot_number', 'product.name', 'product.sku'];

    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['low_stock'])) {
            $query->whereColumn('available_stock', '<=', 'reorder_level');
        }

        if (! empty($filters['out_of_stock'])) {
            $query->where('available_stock', '<=', 0);
        }
    }

    public function findStock(
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?string $batch = null
    ): ?Inventory {
        return $this->query()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->when($batch !== null, fn (Builder $q) => $q->where('batch_lot_number', $batch))
            ->when($batch === null, fn (Builder $q) => $q->whereNull('batch_lot_number'))
            ->lockForUpdate()
            ->first();
    }

    public function lowStockItems(): Collection
    {
        return $this->query()
            ->with(['product', 'variant', 'warehouse'])
            ->whereColumn('available_stock', '<=', 'reorder_level')
            ->where('available_stock', '>', 0)
            ->get();
    }

    public function outOfStockItems(): Collection
    {
        return $this->query()
            ->with(['product', 'variant', 'warehouse'])
            ->where('available_stock', '<=', 0)
            ->get();
    }
}
