<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends AdminController
{
    public function __construct(protected CategoryService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());

        return view('admin.categories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request)
    {
        $this->service->create($this->payload($request));

        return $this->success('Category created successfully.', 'admin.categories.index');
    }

    public function show(Category $category)
    {
        $category->loadCount('products');
        $category->load([
            'products' => fn ($q) => $q->select('id', 'name', 'sku', 'category_id', 'main_image', 'regular_price', 'sale_price', 'is_active')->orderBy('name'),
            'products.collections:id,name,slug',
        ]);

        return view('admin.categories.show', ['item' => $category]);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', ['item' => $category]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->service->update($category, $this->payload($request, $category));

        return $this->success('Category updated successfully.', 'admin.categories.index');
    }

    public function destroy(Category $category)
    {
        $this->service->delete($category);

        return $this->success('Category deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! $ids) {
            return $this->error('Please select at least one category.');
        }

        if ($action === 'delete') {
            $this->service->bulkDelete($ids);

            return $this->success('Selected categories deleted. Linked products were unassigned.');
        }

        return $this->error('Invalid bulk action.');
    }

    /**
     * @return array{name: string, image?: string|null}
     */
    private function payload(CategoryRequest $request, ?Category $category = null): array
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category?->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('uploads/categories', 'public');
        } else {
            unset($data['image']);
        }

        return $data;
    }
}
