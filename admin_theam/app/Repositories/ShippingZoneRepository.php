<?php

namespace App\Repositories;

use App\Models\ShippingZone;

class ShippingZoneRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
);

    public function __construct(ShippingZone $model)
    {
        parent::__construct($model);
    }
}
