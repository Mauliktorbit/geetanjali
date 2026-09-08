<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CollectionRequest;
use App\Models\Collection;
use App\Services\CollectionService;
use Illuminate\Http\Request;

class CollectionController extends AdminController
{
    public function __construct(protected CollectionService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());

        return view('admin.collections.index', compact('items'));
    }

    public function create()
    {
        return view('admin.collections.form', ['item' => null]);
    }

    public function store(CollectionRequest $request)
    {
        $data = $this->payload($request);
        $this->service->create($data);

        return $this->success('Collection created successfully.', 'admin.collections.index');
    }

    public function show(Collection $collection)
    {
        return view('admin.collections.show', ['item' => $collection]);
    }

    public function edit(Collection $collection)
    {
        return view('admin.collections.form', ['item' => $collection]);
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $data = $this->payload($request, $collection);
        $this->service->update($collection, $data);

        return $this->success('Collection updated successfully.', 'admin.collections.index');
    }

    public function destroy(Collection $collection)
    {
        $this->service->delete($collection);

        return $this->success('Collection deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! $ids) {
            return $this->error('Please select at least one item.');
        }

        if ($action === 'delete') {
            $this->service->bulkDelete($ids);

            return $this->success('Selected collections deleted.');
        }

        if ($action === 'activate') {
            $this->service->bulkUpdate($ids, ['is_active' => true]);

            return $this->success('Selected collections activated.');
        }

        if ($action === 'deactivate') {
            $this->service->bulkUpdate($ids, ['is_active' => false]);

            return $this->success('Selected collections deactivated.');
        }

        return $this->error('Invalid bulk action.');
    }

    private function payload(CollectionRequest $request, ?Collection $collection = null): array
    {
        $data = $request->validated();

        foreach (['image', 'banner'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/collections', 'public');
            } elseif ($collection) {
                unset($data[$fileField]);
            }
        }

        return $data;
    }
}
