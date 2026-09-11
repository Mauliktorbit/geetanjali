<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;

class ReviewRepository extends BaseRepository
{
    protected array $searchable = ['customer_name', 'comment', 'product.name', 'customer.name'];

    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return parent::query()->with(['product:id,name,slug,main_image,deleted_at', 'customer:id,name', 'order:id,order_number']);
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
