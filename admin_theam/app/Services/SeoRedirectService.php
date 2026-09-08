<?php

namespace App\Services;

use App\Repositories\SeoRedirectRepository;

class SeoRedirectService extends BaseService
{
    public function __construct(SeoRedirectRepository $repository)
    {
        parent::__construct($repository);
    }
}
