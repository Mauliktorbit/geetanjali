<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AffiliateRequest;
use App\Models\Affiliate;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class AffiliateController extends AdminController
{
    public function __construct(protected AffiliateService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.affiliates.index', compact('items'));
    }

    public function create()
    {
        return view('admin.affiliates.create');
    }

    public function store(AffiliateRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/affiliates', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Affiliate created successfully.', 'admin.affiliates.index');
    }

    public function show(Affiliate $affiliate)
    {
        return view('admin.affiliates.show', ['item' => $affiliate]);
    }

    public function edit(Affiliate $affiliate)
    {
        return view('admin.affiliates.edit', ['item' => $affiliate]);
    }

    public function update(AffiliateRequest $request, Affiliate $affiliate)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/affiliates', 'public');
            }
        }
        $this->service->update($affiliate, $data);
        return $this->success('Affiliate updated successfully.', 'admin.affiliates.index');
    }

    public function destroy(Affiliate $affiliate)
    {
        $this->service->delete($affiliate);
        return $this->success('Affiliate deleted successfully.');
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
