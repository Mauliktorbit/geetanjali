<?php

namespace App\Repositories;

use App\Models\ShippingMethod;

class ShippingMethodRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'code',
  2 => 'type',
);

    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }
}
