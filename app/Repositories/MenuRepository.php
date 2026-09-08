<?php

namespace App\Repositories;

use App\Models\Menu;

class MenuRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'location',
);

    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }
}
