<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShippingZoneRequest;
use App\Models\ShippingZone;
use App\Services\ShippingZoneService;
use Illuminate\Http\Request;

class ShippingZoneController extends AdminController
{
    public function __construct(protected ShippingZoneService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.shipping-zones.index', compact('items'));
    }

    public function create()
    {
        return view('admin.shipping-zones.create');
    }

    public function store(ShippingZoneRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-zones', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('ShippingZone created successfully.', 'admin.shipping-zones.index');
    }

    public function show(ShippingZone $shippingZone)
    {
        return view('admin.shipping-zones.show', ['item' => $shippingZone]);
    }

    public function edit(ShippingZone $shippingZone)
    {
        return view('admin.shipping-zones.edit', ['item' => $shippingZone]);
    }

    public function update(ShippingZoneRequest $request, ShippingZone $shippingZone)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-zones', 'public');
            }
        }
        $this->service->update($shippingZone, $data);
        return $this->success('ShippingZone updated successfully.', 'admin.shipping-zones.index');
    }

    public function destroy(ShippingZone $shippingZone)
    {
        $this->service->delete($shippingZone);
        return $this->success('ShippingZone deleted successfully.');
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
