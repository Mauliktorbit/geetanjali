<?php

namespace App\Repositories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

class CollectionRepository extends BaseRepository
{
    protected array $searchable = ['name'];

    protected array $sortable = [
        'status' => 'is_active',
    ];

    protected string $defaultSort = 'sort_order';

    protected string $defaultDirection = 'asc';

    public function __construct(Collection $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

}
