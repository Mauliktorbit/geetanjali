<?php

namespace App\Repositories;

use App\Models\ReturnReason;

class ReturnReasonRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'type',
);

    public function __construct(ReturnReason $model)
    {
        parent::__construct($model);
    }
}
