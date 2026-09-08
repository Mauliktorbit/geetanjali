<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\WarehouseRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class WarehouseController extends AdminController
{
    public function __construct(protected WarehouseService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.warehouses.index', compact('items'));
    }

    public function create()
    {
        return view('admin.warehouses.create');
    }

    public function store(WarehouseRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/warehouses', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Warehouse created successfully.', 'admin.warehouses.index');
    }

    public function show(Warehouse $warehouse)
    {
        return view('admin.warehouses.show', ['item' => $warehouse]);
    }

    public function edit(Warehouse $warehouse)
    {
        return view('admin.warehouses.edit', ['item' => $warehouse]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/warehouses', 'public');
            }
        }
        $this->service->update($warehouse, $data);
        return $this->success('Warehouse updated successfully.', 'admin.warehouses.index');
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->service->delete($warehouse);
        return $this->success('Warehouse deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');
        if (!$ids) {
            return $this->error('Please select at least one item.');
        }
        if ($action === 'delete') {
            $this->service->bulkDelete($ids);
            return $this->success('Selected items deleted.');
        }
        if ($action === 'activate') {
            $this->service->bulkUpdate($ids, ['is_active' => true]);
            return $this->success('Selected items activated.');
        }
        if ($action === 'deactivate') {
            $this->service->bulkUpdate($ids, ['is_active' => false]);
            return $this->success('Selected items deactivated.');
        }
        return $this->error('Invalid bulk action.');
    }
}
