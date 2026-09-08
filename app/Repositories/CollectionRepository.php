<?php

namespace App\Repositories;

use App\Models\Collection;

class CollectionRepository extends BaseRepository
{
    protected array $searchable = [
        'name',
        'slug',
        'type',
    ];

    public function __construct(Collection $model)
    {
        parent::__construct($model);
    }
}
