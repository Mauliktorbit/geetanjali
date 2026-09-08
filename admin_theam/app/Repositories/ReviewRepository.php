<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;

class ReviewRepository extends BaseRepository
{
    protected array $searchable = ['customer_name', 'title', 'comment'];

    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }
    }
}
