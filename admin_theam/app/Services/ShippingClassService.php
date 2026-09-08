<?php

namespace App\Services;

use App\Repositories\ShippingClassRepository;

class ShippingClassService extends BaseService
{
    public function __construct(ShippingClassRepository $repository)
    {
        parent::__construct($repository);
    }
}
