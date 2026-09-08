<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CustomerRepository extends BaseRepository
{
    protected array $searchable = ['name', 'email', 'phone', 'company_name', 'gstin'];

    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        if (isset($filters['is_blocked'])) {
            $query->where('is_blocked', (bool) $filters['is_blocked']);
        }

        if (isset($filters['is_verified'])) {
            $query->where('is_verified', (bool) $filters['is_verified']);
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
