<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    public function __construct(protected BaseRepository $repository) {}

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function find(int $id, array $with = []): ?Model
    {
        return $this->repository->find($id, $with);
    }

    public function findOrFail(int $id, array $with = []): Model
    {
        return $this->repository->findOrFail($id, $with);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $model = $this->repository->create($data);
            app(AuditLogService::class)->log('created', $model, null, $model->toArray());
            return $model;
        });
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $old = $model->toArray();
            $updated = $this->repository->update($model, $data);
            app(AuditLogService::class)->log('updated', $updated, $old, $updated->toArray());
            return $updated;
        });
    }

    public function delete(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            $old = $model->toArray();
            $result = $this->repository->delete($model);
            app(AuditLogService::class)->log('deleted', $model, $old, null);
            return $result;
        });
    }

    public function bulkUpdate(array $ids, array $data): int
    {
        return DB::transaction(function () use ($ids, $data) {
            $count = $this->repository->bulkUpdate($ids, $data);
            app(AuditLogService::class)->log('bulk_updated', null, ['ids' => $ids], $data);
            return $count;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = $this->repository->bulkDelete($ids);
            app(AuditLogService::class)->log('bulk_deleted', null, ['ids' => $ids], null);
            return $count;
        });
    }
}
