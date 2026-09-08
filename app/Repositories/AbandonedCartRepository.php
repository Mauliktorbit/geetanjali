<?php

namespace App\Repositories;

use App\Models\AbandonedCart;
use Illuminate\Database\Eloquent\Builder;

class AbandonedCartRepository extends BaseRepository
{
    protected array $searchable = ['email', 'phone'];

    public function __construct(AbandonedCart $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['recovery_status'])) {
            $query->where('recovery_status', $filters['recovery_status']);
        }

        if (! empty($filters['min_value'])) {
            $query->where('cart_value', '>=', $filters['min_value']);
        }
    }
}
