<?php

namespace App\Repositories;

use App\Models\Enquiry;

class EnquiryRepository extends BaseRepository
{
    protected array $searchable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
    ];

    public function __construct(Enquiry $model)
    {
        parent::__construct($model);
    }
}
