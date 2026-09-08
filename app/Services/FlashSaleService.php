<?php

namespace App\Services;

use App\Repositories\FlashSaleRepository;

class FlashSaleService extends BaseService
{
    public function __construct(FlashSaleRepository $repository)
    {
        parent::__construct($repository);
    }
}
