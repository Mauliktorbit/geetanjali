<?php

namespace App\Repositories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;

class OfferRepository extends BaseRepository
{
    protected array $searchable = ['title', 'promo_code', 'label', 'discount_display'];

    public function __construct(Offer $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return parent::query()->with('offerCategory');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }
    }

    protected function applySorting(Builder $query, array $filters): void
    {
        $sort = $filters['sort'] ?? 'sort_order';
        $direction = strtolower((string) ($filters['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $direction)->orderByDesc('id');
    }
}
