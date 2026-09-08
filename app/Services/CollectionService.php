<?php

namespace App\Services;

use App\Repositories\CollectionRepository;

class CollectionService extends BaseService
{
    public function __construct(CollectionRepository $repository)
    {
        parent::__construct($repository);
    }
}
