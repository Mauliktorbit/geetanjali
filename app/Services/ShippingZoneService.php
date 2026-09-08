<?php

namespace App\Services;

use App\Repositories\ShippingZoneRepository;

class ShippingZoneService extends BaseService
{
    public function __construct(ShippingZoneRepository $repository)
    {
        parent::__construct($repository);
    }
}
