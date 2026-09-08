<?php

namespace App\Repositories;

use App\Models\Banner;

class BannerRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
  1 => 'type',
);

    public function __construct(Banner $model)
    {
        parent::__construct($model);
    }
}
