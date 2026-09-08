<?php

namespace App\Repositories;

use App\Models\HomepageSection;

class HomepageSectionRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
  1 => 'type',
);

    public function __construct(HomepageSection $model)
    {
        parent::__construct($model);
    }
}
