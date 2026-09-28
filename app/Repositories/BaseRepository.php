<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    public function __construct(protected Model $model) {}

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function find(int $id, array $with = []): ?Model
    {
        return $this->query()->with($with)->find($id);
    }

    public function findOrFail(int $id, array $with = []): Model
    {
        return $this->query()->with($with)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($filters['per_page'] ?? $perPage)->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->query();
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->get();
    }

    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->query()->whereIn('id', $ids)->update($data);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->query()->whereIn('id', $ids)->delete();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search']) && property_exists($this, 'searchable')) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchable as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        if (str_contains($relColumn, '.')) {
                            [$nestedRelation, $nestedColumn] = explode('.', $relColumn, 2);
                            $q->orWhereHas($relation.'.'.$nestedRelation, fn (Builder $rq) => $rq->where($nestedColumn, 'like', "%{$search}%"));
                        } else {
                            $q->orWhereHas($relation, fn (Builder $rq) => $rq->where($relColumn, 'like', "%{$search}%"));
                        }
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($this->model->isFillable('status')) {
                $query->where('status', $filters['status']);
            } elseif ($this->model->isFillable('is_active')) {
                $query->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN) || $filters['status'] === 'active');
            }
        }

        if (! empty($filters['is_active']) || (isset($filters['is_active']) && $filters['is_active'] === '0')) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Request keys or columns that may be used for listing sort.
     * Numeric keys are treated as both the request key and the SQL column.
     *
     * @var array<int|string, string>
     */
    protected array $sortable = [];

    protected string $defaultSort = 'created_at';

    protected string $defaultDirection = 'desc';

    protected function applySorting(Builder $query, array $filters): void
    {
        [$column, $direction] = $this->resolveSort($filters);
        $query->orderBy($this->qualifySortColumn($column), $direction);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: string, 1: string}
     */
    protected function resolveSort(array $filters): array
    {
        $map = $this->sortableColumns();
        $sort = trim((string) ($filters['sort'] ?? ''));
        $direction = strtolower(trim((string) ($filters['direction'] ?? '')));

        if ($sort !== '' && preg_match('/^([a-z0-9_]+)_(asc|desc)$/i', $sort, $matches)) {
            $sort = $matches[1];
            $direction = strtolower($matches[2]);
        }

        if ($sort === '' || ! isset($map[$sort])) {
            return [$this->defaultSort, $this->defaultDirection];
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $this->defaultDirection;
        }

        return [$map[$sort], $direction];
    }

    /**
     * @return array<string, string>
     */
    protected function sortableColumns(): array
    {
        $defaults = [
            'id', 'name', 'title', 'slug', 'sku', 'email', 'phone', 'code',
            'status', 'is_active', 'is_blocked', 'sort_order', 'created_at', 'updated_at',
        ];

        $map = [];
        foreach (array_merge($defaults, $this->sortable) as $key => $column) {
            if (is_int($key)) {
                $map[$column] = $column;
            } else {
                $map[$key] = $column;
            }
        }

        return $map;
    }

    protected function qualifySortColumn(string $column): string
    {
        if (str_contains($column, '.') || str_contains($column, ' ') || str_ends_with($column, '_count')) {
            return $column;
        }

        return $this->model->getTable().'.'.$column;
    }
}
