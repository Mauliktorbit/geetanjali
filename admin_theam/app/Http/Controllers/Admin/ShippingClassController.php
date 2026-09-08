<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShippingClassRequest;
use App\Models\ShippingClass;
use App\Services\ShippingClassService;
use Illuminate\Http\Request;

class ShippingClassController extends AdminController
{
    public function __construct(protected ShippingClassService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.shipping-classes.index', compact('items'));
    }

    public function create()
    {
        return view('admin.shipping-classes.create');
    }

    public function store(ShippingClassRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-classes', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('ShippingClass created successfully.', 'admin.shipping-classes.index');
    }

    public function show(ShippingClass $shippingClass)
    {
        return view('admin.shipping-classes.show', ['item' => $shippingClass]);
    }

    public function edit(ShippingClass $shippingClass)
    {
        return view('admin.shipping-classes.edit', ['item' => $shippingClass]);
    }

    public function update(ShippingClassRequest $request, ShippingClass $shippingClass)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/shipping-classes', 'public');
            }
        }
        $this->service->update($shippingClass, $data);
        return $this->success('ShippingClass updated successfully.', 'admin.shipping-classes.index');
    }

    public function destroy(ShippingClass $shippingClass)
    {
        $this->service->delete($shippingClass);
        return $this->success('ShippingClass deleted successfully.');
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
