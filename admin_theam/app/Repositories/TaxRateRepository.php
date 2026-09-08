<?php

namespace App\Repositories;

use App\Models\TaxRate;

class TaxRateRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'hsn_sac',
);

    public function __construct(TaxRate $model)
    {
        parent::__construct($model);
    }
}
