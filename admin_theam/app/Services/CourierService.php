<?php

namespace App\Services;

use App\Repositories\CourierRepository;

class CourierService extends BaseService
{
    public function __construct(CourierRepository $repository)
    {
        parent::__construct($repository);
    }
}
