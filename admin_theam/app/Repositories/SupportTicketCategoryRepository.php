<?php

namespace App\Repositories;

use App\Models\SupportTicketCategory;

class SupportTicketCategoryRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'name',
);

    public function __construct(SupportTicketCategory $model)
    {
        parent::__construct($model);
    }
}
