<?php

namespace App\Repositories;

use App\Models\Affiliate;

class AffiliateRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'email',
  2 => 'code',
);

    public function __construct(Affiliate $model)
    {
        parent::__construct($model);
    }
}
