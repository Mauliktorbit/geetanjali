<?php

namespace App\Repositories;

use App\Models\Integration;

class IntegrationRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'provider',
  2 => 'category',
);

    public function __construct(Integration $model)
    {
        parent::__construct($model);
    }
}
