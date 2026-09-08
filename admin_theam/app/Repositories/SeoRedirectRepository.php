<?php

namespace App\Repositories;

use App\Models\SeoRedirect;

class SeoRedirectRepository extends BaseRepository
{
    protected array $searchable = array (
  0 => 'from_path',
  1 => 'to_path',
);

    public function __construct(SeoRedirect $model)
    {
        parent::__construct($model);
    }
}
