<?php

namespace App\Repositories;

use App\Models\Campaign;

class CampaignRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
  1 => 'channel',
  2 => 'subject',
);

    public function __construct(Campaign $model)
    {
        parent::__construct($model);
    }
}
