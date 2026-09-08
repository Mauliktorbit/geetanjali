<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CampaignRequest;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class CampaignController extends AdminController
{
    public function __construct(protected CampaignService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.campaigns.index', compact('items'));
    }

    public function create()
    {
        return view('admin.campaigns.create');
    }

    public function store(CampaignRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/campaigns', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Campaign created successfully.', 'admin.campaigns.index');
    }

    public function show(Campaign $campaign)
    {
        return view('admin.campaigns.show', ['item' => $campaign]);
    }

    public function edit(Campaign $campaign)
    {
        return view('admin.campaigns.edit', ['item' => $campaign]);
    }

    public function update(CampaignRequest $request, Campaign $campaign)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/campaigns', 'public');
            }
        }
        $this->service->update($campaign, $data);
        return $this->success('Campaign updated successfully.', 'admin.campaigns.index');
    }

    public function destroy(Campaign $campaign)
    {
        $this->service->delete($campaign);
        return $this->success('Campaign deleted successfully.');
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
