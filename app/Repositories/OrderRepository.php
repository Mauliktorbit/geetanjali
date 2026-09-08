<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderRepository extends BaseRepository
{
    protected array $searchable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'tracking_number',
    ];

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (! empty($filters['shipping_city'])) {
            $query->where('shipping_city', $filters['shipping_city']);
        }
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->query()->where('order_number', $orderNumber)->first();
    }

    public function statusCounts(?string $from = null, ?string $to = null): Collection
    {
        $query = $this->query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function salesSum(string $from, string $to): float
    {
        return (float) $this->query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->sum('grand_total');
    }
}
