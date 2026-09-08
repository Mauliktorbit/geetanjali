<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'slug',
);

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
}
