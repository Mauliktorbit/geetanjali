<?php

namespace App\Repositories;

use App\Models\Warehouse;

class WarehouseRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'code',
  2 => 'city',
);

    public function __construct(Warehouse $model)
    {
        parent::__construct($model);
    }
}
