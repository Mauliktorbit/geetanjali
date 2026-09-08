<?php

namespace App\Services;

use App\Repositories\SupportTicketCategoryRepository;

class SupportTicketCategoryService extends BaseService
{
    public function __construct(SupportTicketCategoryRepository $repository)
    {
        parent::__construct($repository);
    }
}
