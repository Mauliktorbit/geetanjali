<?php

namespace App\Services;

use App\Repositories\StoreLocationRepository;

class StoreLocationService extends BaseService
{
    public function __construct(StoreLocationRepository $repository)
    {
        parent::__construct($repository);
    }
}
