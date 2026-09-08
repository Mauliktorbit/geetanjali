<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Services\ShippingMethodService;
use Illuminate\Http\Request;

class ShippingMethodController extends AdminController
{
    public function __construct(protected ShippingMethodService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.shipping-methods.index', compact('items'));
    }

    public function create()
    {
        return view('admin.shipping-methods.create');
    }

    public function store(ShippingMethodRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-methods', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('ShippingMethod created successfully.', 'admin.shipping-methods.index');
    }

    public function show(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping-methods.show', ['item' => $shippingMethod]);
    }

    public function edit(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping-methods.edit', ['item' => $shippingMethod]);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $shippingMethod)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-methods', 'public');
            }
        }
        $this->service->update($shippingMethod, $data);
        return $this->success('ShippingMethod updated successfully.', 'admin.shipping-methods.index');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $this->service->delete($shippingMethod);
        return $this->success('ShippingMethod deleted successfully.');
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
