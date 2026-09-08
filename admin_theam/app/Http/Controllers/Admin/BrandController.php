<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends AdminController
{
    public function __construct(protected BrandService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.brands.index', compact('items'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/brands', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Brand created successfully.', 'admin.brands.index');
    }

    public function show(Brand $brand)
    {
        return view('admin.brands.show', ['item' => $brand]);
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', ['item' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/brands', 'public');
            }
        }
        $this->service->update($brand, $data);
        return $this->success('Brand updated successfully.', 'admin.brands.index');
    }

    public function destroy(Brand $brand)
    {
        $this->service->delete($brand);
        return $this->success('Brand deleted successfully.');
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
