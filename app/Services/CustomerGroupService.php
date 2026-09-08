<?php

namespace App\Services;

use App\Repositories\CustomerGroupRepository;

class CustomerGroupService extends BaseService
{
    public function __construct(CustomerGroupRepository $repository)
    {
        parent::__construct($repository);
    }
}
