<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'title',
  1 => 'category',
  2 => 'reference',
);

    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }
}
