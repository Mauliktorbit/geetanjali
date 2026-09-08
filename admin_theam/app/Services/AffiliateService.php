<?php

namespace App\Services;

use App\Repositories\AffiliateRepository;

class AffiliateService extends BaseService
{
    public function __construct(AffiliateRepository $repository)
    {
        parent::__construct($repository);
    }
}
