<?php

namespace App\Repositories;

use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Builder;

class ReturnRequestRepository extends BaseRepository
{
    protected array $searchable = ['return_number', 'customer_reason', 'status'];

    public function __construct(ReturnRequest $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
    }
}
