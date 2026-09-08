<?php

namespace App\Repositories;

use App\Models\Page;

class PageRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
  1 => 'slug',
  2 => 'type',
);

    public function __construct(Page $model)
    {
        parent::__construct($model);
    }
}
