<?php

namespace App\Repositories;

use App\Models\ShippingClass;

class ShippingClassRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'slug',
);

    public function __construct(ShippingClass $model)
    {
        parent::__construct($model);
    }
}
