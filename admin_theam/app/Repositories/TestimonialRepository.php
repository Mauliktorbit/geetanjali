<?php

namespace App\Repositories;

use App\Models\Testimonial;

class TestimonialRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'content',
);

    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }
}
