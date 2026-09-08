<?php

namespace App\Services;

use App\Repositories\TestimonialRepository;

class TestimonialService extends BaseService
{
    public function __construct(TestimonialRepository $repository)
    {
        parent::__construct($repository);
    }
}
