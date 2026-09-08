<?php

namespace App\Repositories;

use App\Models\Faq;

class FaqRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'question',
  1 => 'category',
);

    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }
}
