<?php

namespace App\Services;

use App\Repositories\HomepageSectionRepository;

class HomepageSectionService extends BaseService
{
    public function __construct(HomepageSectionRepository $repository)
    {
        parent::__construct($repository);
    }
}
