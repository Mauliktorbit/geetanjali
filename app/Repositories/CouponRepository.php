<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;

class CouponRepository extends BaseRepository
{
    protected array $searchable = ['code', 'name', 'offers.title'];

    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return parent::query()->with(['offers', 'offersWithCode']);
    }
}
