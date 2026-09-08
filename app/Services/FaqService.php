<?php

namespace App\Services;

use App\Repositories\FaqRepository;

class FaqService extends BaseService
{
    public function __construct(FaqRepository $repository)
    {
        parent::__construct($repository);
    }
}
