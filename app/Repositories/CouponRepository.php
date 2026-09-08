<?php

namespace App\Repositories;

use App\Models\Coupon;

class CouponRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'code',
  1 => 'name',
);

    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }
}
