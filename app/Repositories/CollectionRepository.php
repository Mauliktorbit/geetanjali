<?php

namespace App\Repositories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

class CollectionRepository extends BaseRepository
{
    protected array $searchable = ['name'];

    public function __construct(Collection $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function applySorting(Builder $query, array $filters): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
