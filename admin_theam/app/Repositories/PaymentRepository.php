<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentRepository extends BaseRepository
{
    protected array $searchable = ['transaction_id', 'payment_method', 'gateway'];

    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['settlement_status'])) {
            $query->where('settlement_status', $filters['settlement_status']);
        }
    }
}
