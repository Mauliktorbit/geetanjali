<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IntegrationRequest;
use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Http\Request;

class IntegrationController extends AdminController
{
    public function __construct(protected IntegrationService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.integrations.index', compact('items'));
    }

    public function create()
    {
        return view('admin.integrations.create');
    }

    public function store(IntegrationRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/integrations', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Integration created successfully.', 'admin.integrations.index');
    }

    public function show(Integration $integration)
    {
        return view('admin.integrations.show', ['item' => $integration]);
    }

    public function edit(Integration $integration)
    {
        return view('admin.integrations.edit', ['item' => $integration]);
    }

    public function update(IntegrationRequest $request, Integration $integration)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/integrations', 'public');
            }
        }
        $this->service->update($integration, $data);
        return $this->success('Integration updated successfully.', 'admin.integrations.index');
    }

    public function destroy(Integration $integration)
    {
        $this->service->delete($integration);
        return $this->success('Integration deleted successfully.');
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
