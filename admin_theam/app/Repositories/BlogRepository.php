<?php

namespace App\Repositories;

use App\Models\Blog;

class BlogRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
  1 => 'slug',
);

    public function __construct(Blog $model)
    {
        parent::__construct($model);
    }
}
