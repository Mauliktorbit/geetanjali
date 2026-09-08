<?php

namespace App\Services;

use App\Repositories\BlogRepository;

class BlogService extends BaseService
{
    public function __construct(BlogRepository $repository)
    {
        parent::__construct($repository);
    }
}
