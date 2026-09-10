<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $payload = $this->fromName($data['name'] ?? '');
        if (! empty($data['image'])) {
            $payload['image'] = $data['image'];
        }

        return parent::create($payload);
    }

    public function update(Model $model, array $data): Model
    {
        $existing = $model instanceof Category ? $model : null;
        $payload = $this->fromName($data['name'] ?? '', $existing);
        if (array_key_exists('image', $data)) {
            $payload['image'] = $data['image'];
        }

        return parent::update($model, $payload);
    }

    public function delete(Model $model): bool
    {
        if ($model instanceof Category) {
            $this->detachProducts([(int) $model->id]);
        }

        return parent::delete($model);
    }

    public function bulkDelete(array $ids): int
    {
        $this->detachProducts($ids);

        return parent::bulkDelete($ids);
    }

    /**
     * @return array{name: string, slug: string, is_active?: bool, display_order?: int}
     */
    private function fromName(string $name, ?Category $existing = null): array
    {
        $name = trim($name);
        $payload = [
            'name' => $name,
            'slug' => $this->uniqueSlug(Str::slug($name) ?: 'category', $existing?->id),
        ];

        if ($existing === null) {
            $payload['is_active'] = true;
            $payload['display_order'] = (int) Category::query()->max('display_order') + 1;
        }

        return $payload;
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            Category::query()
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
     * @param  list<int|string>  $categoryIds
     */
    private function detachProducts(array $categoryIds): void
    {
        $ids = array_values(array_filter(array_map('intval', $categoryIds)));
        if ($ids === []) {
            return;
        }

        Product::query()->whereIn('category_id', $ids)->update(['category_id' => null]);
        Product::query()->whereIn('subcategory_id', $ids)->update(['subcategory_id' => null]);
    }
}
