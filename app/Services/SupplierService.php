<?php

namespace App\Services;

use App\Repositories\SupplierRepository;

class SupplierService extends BaseService
{
    public function __construct(SupplierRepository $repository)
    {
        parent::__construct($repository);
    }
}
