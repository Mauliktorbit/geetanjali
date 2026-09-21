<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CustomerRepository extends BaseRepository
{
    protected array $searchable = ['name', 'email', 'phone'];

    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        $status = strtolower((string) ($filters['status'] ?? ''));
        if (in_array($status, ['blocked', 'inactive', '0'], true)) {
            $query->where('is_blocked', true);
        } elseif (in_array($status, ['active', '1'], true)) {
            $query->where('is_blocked', false);
        }
    }

    public function findByEmail(string $email): ?Customer
    {
        return $this->query()->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?Customer
    {
        return $this->query()->where('phone', $phone)->first();
    }
}
