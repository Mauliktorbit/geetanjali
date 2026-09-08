<?php

namespace App\Repositories;

use App\Models\CustomerGroup;

class CustomerGroupRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'slug',
);

    public function __construct(CustomerGroup $model)
    {
        parent::__construct($model);
    }
}
