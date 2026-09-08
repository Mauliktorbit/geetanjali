<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderRepository extends BaseRepository
{
    protected array $searchable = ['po_number', 'notes'];

    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
    }
}
