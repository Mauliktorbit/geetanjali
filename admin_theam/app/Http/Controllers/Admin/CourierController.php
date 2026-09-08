<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CourierRequest;
use App\Models\Courier;
use App\Services\CourierService;
use Illuminate\Http\Request;

class CourierController extends AdminController
{
    public function __construct(protected CourierService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.couriers.index', compact('items'));
    }

    public function create()
    {
        return view('admin.couriers.create');
    }

    public function store(CourierRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/couriers', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Courier created successfully.', 'admin.couriers.index');
    }

    public function show(Courier $courier)
    {
        return view('admin.couriers.show', ['item' => $courier]);
    }

    public function edit(Courier $courier)
    {
        return view('admin.couriers.edit', ['item' => $courier]);
    }

    public function update(CourierRequest $request, Courier $courier)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/couriers', 'public');
            }
        }
        $this->service->update($courier, $data);
        return $this->success('Courier updated successfully.', 'admin.couriers.index');
    }

    public function destroy(Courier $courier)
    {
        $this->service->delete($courier);
        return $this->success('Courier deleted successfully.');
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
