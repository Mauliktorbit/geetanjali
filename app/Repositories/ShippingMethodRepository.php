<?php

namespace App\Repositories;

use App\Models\ShippingMethod;

class ShippingMethodRepository extends BaseRepository
{
    protected array $searchable = ['name', 'code', 'type'];

    protected array $sortable = [
        'rule' => 'name',
        'charge' => 'rate',
        'delivery' => 'estimated_delivery',
        'free' => 'free_shipping_threshold',
        'status' => 'is_active',
        'rate',
        'estimated_delivery',
        'free_shipping_threshold',
    ];

    protected string $defaultSort = 'name';

    protected string $defaultDirection = 'asc';

    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }
}
