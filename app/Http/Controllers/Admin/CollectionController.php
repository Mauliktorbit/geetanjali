<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CollectionRequest;
use App\Models\Collection;
use App\Services\CollectionService;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CollectionController extends AdminController
{
    public function __construct(protected CollectionService $service) {}

    public function index(Request $request)
    {
        StorefrontCatalogService::ensurePageCollections();
        $items = $this->service->paginate($request->all());

        return view('admin.collections.index', compact('items'));
    }

    public function create()
    {
        return view('admin.collections.create');
    }

    public function store(CollectionRequest $request)
    {
        $this->service->create($this->payload($request));

        return $this->success('Collection created successfully.', 'admin.collections.index');
    }

    public function show(Collection $collection)
    {
        $collection->loadCount('products');
        $collection->load([
            'products' => fn ($q) => $q->select('products.id', 'products.name', 'products.sku', 'products.category_id', 'products.main_image', 'products.regular_price', 'products.sale_price', 'products.is_active')->orderBy('products.name'),
            'products.category:id,name',
        ]);

        return view('admin.collections.show', ['item' => $collection]);
    }

    public function edit(Collection $collection)
    {
        return view('admin.collections.edit', ['item' => $collection]);
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $this->service->update($collection, $this->payload($request, $collection));

        return $this->success('Collection updated successfully.', 'admin.collections.index');
    }

    public function destroy(Collection $collection)
    {
        try {
            $this->service->delete($collection);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('Collection deleted successfully.');
    }

    public function toggle(Collection $collection)
    {
        $active = ! $collection->is_active;
        $this->service->setActive($collection, $active);

        return $this->success($active
            ? $collection->name.' is now active on the website.'
            : $collection->name.' is now inactive and hidden from the website.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! $ids) {
            return $this->error('Please select at least one collection.');
        }

        if ($action === 'activate') {
            $this->service->bulkUpdate($ids, ['is_active' => true]);

            return $this->success('Selected collections are now active.');
        }

        if ($action === 'deactivate') {
            $this->service->bulkUpdate($ids, ['is_active' => false]);

            return $this->success('Selected collections are now inactive and hidden from the website.');
        }

        if ($action !== 'delete') {
            return $this->error('Invalid bulk action.');
        }

        try {
            $this->service->bulkDelete($ids);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('Selected collections deleted.');
    }

    /**
     * @return array{name: string, description?: string|null, image?: string, is_active: bool}
     */
    private function payload(CollectionRequest $request, ?Collection $collection = null): array
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($collection?->image) {
                Storage::disk('public')->delete($collection->image);
            }
            $data['image'] = $request->file('image')->store('uploads/collections', 'public');
        } else {
            unset($data['image']);
        }

        return $data;
    }
}
