<?php

namespace App\Repositories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;

class OfferRepository extends BaseRepository
{
    protected array $searchable = ['title', 'promo_code', 'label', 'discount_display'];

    protected array $sortable = [
        'offer' => 'title',
        'valid' => 'ends_at',
        'status' => 'is_active',
        'category',
        'ends_at',
    ];

    protected string $defaultSort = 'sort_order';

    protected string $defaultDirection = 'asc';

    public function __construct(Offer $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return parent::query()->with(['offerCategory', 'coupon']);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }
    }

}
