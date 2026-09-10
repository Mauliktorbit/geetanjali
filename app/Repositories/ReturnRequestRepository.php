<?php

namespace App\Repositories;

use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReturnRequestRepository extends BaseRepository
{
    protected array $searchable = [
        'return_number',
        'customer_reason',
        'order.order_number',
        'order.customer_name',
        'order.customer_phone',
        'customer.name',
        'customer.phone',
        'items.orderItem.product_name',
    ];

    public function __construct(ReturnRequest $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with(['order', 'customer', 'items.orderItem']);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($filters['per_page'] ?? $perPage)->withQueryString();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $status = $filters['status'] ?? null;
        unset($filters['status']);

        parent::applyFilters($query, $filters);

        if ($status !== null && $status !== '') {
            $query->whereIn('status', ReturnStatus::filterKeys((string) $status));
        }
    }
}
