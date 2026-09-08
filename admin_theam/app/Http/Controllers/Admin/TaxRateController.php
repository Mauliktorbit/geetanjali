<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TaxRateRequest;
use App\Models\TaxRate;
use App\Services\TaxRateService;
use Illuminate\Http\Request;

class TaxRateController extends AdminController
{
    public function __construct(protected TaxRateService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.tax-rates.index', compact('items'));
    }

    public function create()
    {
        return view('admin.tax-rates.create');
    }

    public function store(TaxRateRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/tax-rates', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('TaxRate created successfully.', 'admin.tax-rates.index');
    }

    public function show(TaxRate $taxRate)
    {
        return view('admin.tax-rates.show', ['item' => $taxRate]);
    }

    public function edit(TaxRate $taxRate)
    {
        return view('admin.tax-rates.edit', ['item' => $taxRate]);
    }

    public function update(TaxRateRequest $request, TaxRate $taxRate)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/tax-rates', 'public');
            }
        }
        $this->service->update($taxRate, $data);
        return $this->success('TaxRate updated successfully.', 'admin.tax-rates.index');
    }

    public function destroy(TaxRate $taxRate)
    {
        $this->service->delete($taxRate);
        return $this->success('TaxRate deleted successfully.');
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
