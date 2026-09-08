<?php

namespace App\Repositories;

use App\Models\GiftCard;

class GiftCardRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'code',
);

    public function __construct(GiftCard $model)
    {
        parent::__construct($model);
    }
}
