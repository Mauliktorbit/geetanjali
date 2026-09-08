<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'company_name',
  2 => 'email',
  3 => 'phone',
  4 => 'gstin',
);

    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }
}
