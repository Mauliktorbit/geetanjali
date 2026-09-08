<?php

namespace App\Repositories;

use App\Models\Attribute;

class AttributeRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'slug',
);

    public function __construct(Attribute $model)
    {
        parent::__construct($model);
    }
}
