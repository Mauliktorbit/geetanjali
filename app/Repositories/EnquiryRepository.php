<?php

namespace App\Repositories;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Builder;

class EnquiryRepository extends BaseRepository
{
    protected array $searchable = [
        'name',
        'email',
        'phone',
        'message',
    ];

    public function __construct(Enquiry $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $status = $filters['status'] ?? '';
        unset($filters['status']);

        parent::applyFilters($query, $filters);

        if ($status === 'new') {
            $query->where('status', 'new');
        } elseif ($status === 'read') {
            $query->where('status', '!=', 'new');
        }
    }
}
