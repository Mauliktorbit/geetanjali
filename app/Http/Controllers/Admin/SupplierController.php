<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends AdminController
{
    public function __construct(protected SupplierService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.suppliers.index', compact('items'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(SupplierRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/suppliers', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Supplier created successfully.', 'admin.suppliers.index');
    }

    public function show(Supplier $supplier)
    {
        return view('admin.suppliers.show', ['item' => $supplier]);
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', ['item' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/suppliers', 'public');
            }
        }
        $this->service->update($supplier, $data);
        return $this->success('Supplier updated successfully.', 'admin.suppliers.index');
    }

    public function destroy(Supplier $supplier)
    {
        $this->service->delete($supplier);
        return $this->success('Supplier deleted successfully.');
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
