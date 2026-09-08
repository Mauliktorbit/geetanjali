<?php

namespace App\Repositories;

use App\Models\FlashSale;

class FlashSaleRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
);

    public function __construct(FlashSale $model)
    {
        parent::__construct($model);
    }
}
