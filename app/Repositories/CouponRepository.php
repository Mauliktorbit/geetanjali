<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;

class CouponRepository extends BaseRepository
{
    protected array $searchable = ['code', 'name', 'offers.title'];

    protected array $sortable = [
        'discount' => 'discount_value',
        'dates' => 'starts_at',
        'status' => 'is_active',
        'discount_value',
        'starts_at',
        'ends_at',
    ];

    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return parent::query()->with(['offers', 'offersWithCode']);
    }
}
