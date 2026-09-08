<?php

namespace App\Services;

use App\Repositories\BannerRepository;

class BannerService extends BaseService
{
    public function __construct(BannerRepository $repository)
    {
        parent::__construct($repository);
    }
}
