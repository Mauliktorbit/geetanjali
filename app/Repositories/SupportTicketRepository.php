<?php

namespace App\Repositories;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Builder;

class SupportTicketRepository extends BaseRepository
{
    protected array $searchable = ['ticket_number', 'subject'];

    public function __construct(SupportTicket $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
    }
}
