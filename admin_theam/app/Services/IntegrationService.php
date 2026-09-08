<?php

namespace App\Services;

use App\Repositories\IntegrationRepository;

class IntegrationService extends BaseService
{
    public function __construct(IntegrationRepository $repository)
    {
        parent::__construct($repository);
    }
}
