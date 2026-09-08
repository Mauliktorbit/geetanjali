<?php

namespace App\Services;

use App\Repositories\AttributeRepository;

class AttributeService extends BaseService
{
    public function __construct(AttributeRepository $repository)
    {
        parent::__construct($repository);
    }
}
