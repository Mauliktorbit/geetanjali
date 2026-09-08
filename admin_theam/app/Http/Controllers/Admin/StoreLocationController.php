<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreLocationRequest;
use App\Models\StoreLocation;
use App\Services\StoreLocationService;
use Illuminate\Http\Request;

class StoreLocationController extends AdminController
{
    public function __construct(protected StoreLocationService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.store-locations.index', compact('items'));
    }

    public function create()
    {
        return view('admin.store-locations.create');
    }

    public function store(StoreLocationRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/store-locations', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('StoreLocation created successfully.', 'admin.store-locations.index');
    }

    public function show(StoreLocation $storeLocation)
    {
        return view('admin.store-locations.show', ['item' => $storeLocation]);
    }

    public function edit(StoreLocation $storeLocation)
    {
        return view('admin.store-locations.edit', ['item' => $storeLocation]);
    }

    public function update(StoreLocationRequest $request, StoreLocation $storeLocation)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/store-locations', 'public');
            }
        }
        $this->service->update($storeLocation, $data);
        return $this->success('StoreLocation updated successfully.', 'admin.store-locations.index');
    }

    public function destroy(StoreLocation $storeLocation)
    {
        $this->service->delete($storeLocation);
        return $this->success('StoreLocation deleted successfully.');
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
