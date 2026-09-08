<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ReturnReasonRequest;
use App\Models\ReturnReason;
use App\Services\ReturnReasonService;
use Illuminate\Http\Request;

class ReturnReasonController extends AdminController
{
    public function __construct(protected ReturnReasonService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.return-reasons.index', compact('items'));
    }

    public function create()
    {
        return view('admin.return-reasons.create');
    }

    public function store(ReturnReasonRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/return-reasons', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('ReturnReason created successfully.', 'admin.return-reasons.index');
    }

    public function show(ReturnReason $returnReason)
    {
        return view('admin.return-reasons.show', ['item' => $returnReason]);
    }

    public function edit(ReturnReason $returnReason)
    {
        return view('admin.return-reasons.edit', ['item' => $returnReason]);
    }

    public function update(ReturnReasonRequest $request, ReturnReason $returnReason)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/return-reasons', 'public');
            }
        }
        $this->service->update($returnReason, $data);
        return $this->success('ReturnReason updated successfully.', 'admin.return-reasons.index');
    }

    public function destroy(ReturnReason $returnReason)
    {
        $this->service->delete($returnReason);
        return $this->success('ReturnReason deleted successfully.');
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
