<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        'items.product_name',
    ];

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with(['items.product']);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($filters['per_page'] ?? $perPage)->withQueryString();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->whereIn('status', OrderStatus::filterKeys((string) $filters['status']));
            unset($filters['status']);
        }

        parent::applyFilters($query, $filters);

        if (($filters['payment_status'] ?? '') === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif (($filters['payment_status'] ?? '') === 'pending') {
            $query->where('payment_status', '!=', 'paid');
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
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
