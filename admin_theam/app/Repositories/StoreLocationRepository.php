<?php

namespace App\Repositories;

use App\Models\StoreLocation;

class StoreLocationRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'city',
  2 => 'phone',
);

    public function __construct(StoreLocation $model)
    {
        parent::__construct($model);
    }
}
