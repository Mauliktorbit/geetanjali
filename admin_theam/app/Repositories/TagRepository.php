<?php

namespace App\Repositories;

use App\Models\Tag;

class TagRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'slug',
);

    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }
}
