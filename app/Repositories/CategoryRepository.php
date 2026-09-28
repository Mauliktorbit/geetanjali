<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryRepository extends BaseRepository
{
    protected array $searchable = ['name'];

    protected array $sortable = [
        'products' => 'products_count',
    ];

    protected string $defaultSort = 'id';

    protected string $defaultDirection = 'desc';

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return $this->model->newQuery()
            ->withCount('products')
            ->with([
                'products' => fn ($q) => $q->select('id', 'name', 'sku', 'category_id', 'main_image')->orderBy('name'),
                'products.collections:id,name,slug',
            ]);
    }

    protected function applySorting(Builder $query, array $filters): void
    {
        parent::applySorting($query, $filters);
        $query->orderByDesc($this->model->getTable().'.id');
    }
}
