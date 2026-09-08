<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FlashSaleRequest;
use App\Models\FlashSale;
use App\Services\FlashSaleService;
use Illuminate\Http\Request;

class FlashSaleController extends AdminController
{
    public function __construct(protected FlashSaleService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.flash-sales.index', compact('items'));
    }

    public function create()
    {
        return view('admin.flash-sales.create');
    }

    public function store(FlashSaleRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/flash-sales', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('FlashSale created successfully.', 'admin.flash-sales.index');
    }

    public function show(FlashSale $flashSale)
    {
        return view('admin.flash-sales.show', ['item' => $flashSale]);
    }

    public function edit(FlashSale $flashSale)
    {
        return view('admin.flash-sales.edit', ['item' => $flashSale]);
    }

    public function update(FlashSaleRequest $request, FlashSale $flashSale)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/flash-sales', 'public');
            }
        }
        $this->service->update($flashSale, $data);
        return $this->success('FlashSale updated successfully.', 'admin.flash-sales.index');
    }

    public function destroy(FlashSale $flashSale)
    {
        $this->service->delete($flashSale);
        return $this->success('FlashSale deleted successfully.');
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
