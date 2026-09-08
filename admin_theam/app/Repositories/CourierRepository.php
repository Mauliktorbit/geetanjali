<?php

namespace App\Repositories;

use App\Models\Courier;

class CourierRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'code',
  2 => 'provider',
);

    public function __construct(Courier $model)
    {
        parent::__construct($model);
    }
}
