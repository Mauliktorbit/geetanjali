<?php

namespace App\Services;

use App\Repositories\ReturnReasonRepository;

class ReturnReasonService extends BaseService
{
    public function __construct(ReturnReasonRepository $repository)
    {
        parent::__construct($repository);
    }
}
