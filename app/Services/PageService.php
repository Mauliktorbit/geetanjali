<?php

namespace App\Services;

use App\Repositories\PageRepository;

class PageService extends BaseService
{
    public function __construct(PageRepository $repository)
    {
        parent::__construct($repository);
    }
}
