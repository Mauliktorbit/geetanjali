<?php

namespace App\Services;

use App\Repositories\TagRepository;

class TagService extends BaseService
{
    public function __construct(TagRepository $repository)
    {
        parent::__construct($repository);
    }
}
