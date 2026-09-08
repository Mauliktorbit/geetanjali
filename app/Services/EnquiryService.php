<?php

namespace App\Services;

use App\Repositories\EnquiryRepository;

class EnquiryService extends BaseService
{
    public function __construct(EnquiryRepository $repository)
    {
        parent::__construct($repository);
    }
}
