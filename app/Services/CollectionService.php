<?php

namespace App\Services;

use App\Models\Collection;
use App\Repositories\CollectionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class CollectionService extends BaseService
{
    public function __construct(CollectionRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $payload = $this->fromInput($data);

        return parent::create($payload);
    }

    public function update(Model $model, array $data): Model
    {
        $existing = $model instanceof Collection ? $model : null;
        $payload = $this->fromInput($data, $existing);

        return parent::update($model, $payload);
    }

    public function delete(Model $model): bool
    {
        if ($model instanceof Collection) {
            $this->guardProtected([$model]);
            $model->products()->detach();
        }

        return parent::delete($model);
    }

    public function bulkDelete(array $ids): int
    {
        $collections = Collection::query()->whereIn('id', $ids)->get();
        $deletable = $collections->reject(
            fn (Collection $collection) => StorefrontCatalogService::isProtectedSlug($collection->slug)
        );

        if ($deletable->isEmpty()) {
            throw new RuntimeException('The selected collections are used by the website and cannot be deleted.');
        }

        foreach ($deletable as $collection) {
            $collection->products()->detach();
        }

        return parent::bulkDelete($deletable->modelKeys());
    }

    public function setActive(Collection $collection, bool $active): Model
    {
        return parent::update($collection, ['is_active' => $active]);
    }

    /**
     * @param  array{name?: string, slug?: string|null, description?: string|null, image?: string|null, remove_image?: bool, seo_title?: string|null, seo_description?: string|null, sort_order?: int|null, is_active?: bool}  $data
     * @return array<string, mixed>
     */
    private function fromInput(array $data, ?Collection $existing = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $protected = $existing && StorefrontCatalogService::isProtectedSlug($existing->slug);

        $payload = [
            'name' => $name,
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'seo_title' => filled($data['seo_title'] ?? null) ? trim((string) $data['seo_title']) : null,
            'seo_description' => filled($data['seo_description'] ?? null) ? trim((string) $data['seo_description']) : null,
        ];

        if (array_key_exists('is_active', $data)) {
            $payload['is_active'] = (bool) $data['is_active'];
        } elseif ($existing === null) {
            $payload['is_active'] = true;
        }

        if (array_key_exists('sort_order', $data) && $data['sort_order'] !== null && $data['sort_order'] !== '') {
            $payload['sort_order'] = max(0, (int) $data['sort_order']);
        } elseif ($existing === null) {
            $payload['sort_order'] = (int) Collection::query()->max('sort_order') + 1;
        }

        if ($protected) {
            $payload['slug'] = $existing->slug;
            $payload['type'] = $existing->type ?: $existing->slug;
        } else {
            $source = filled($data['slug'] ?? null) ? (string) $data['slug'] : (Str::slug($name) ?: 'collection');
            $payload['slug'] = $this->uniqueSlug($source, $existing?->id);
            $payload['type'] = StorefrontCatalogService::isProtectedSlug($payload['slug'])
                ? $payload['slug']
                : 'custom';
        }

        if (! empty($data['image'])) {
            $payload['image'] = $data['image'];
        } elseif (! empty($data['remove_image'])) {
            $payload['image'] = null;
        }

        return $payload;
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            Collection::query()
                ->withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  list<Collection>  $collections
     */
    private function guardProtected(array $collections): void
    {
        foreach ($collections as $collection) {
            if (StorefrontCatalogService::isProtectedSlug($collection->slug)) {
                throw new RuntimeException($collection->name.' is used by the website and cannot be deleted.');
            }
        }
    }
}
