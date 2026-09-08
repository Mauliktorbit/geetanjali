<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CustomerGroupRequest;
use App\Models\CustomerGroup;
use App\Services\CustomerGroupService;
use Illuminate\Http\Request;

class CustomerGroupController extends AdminController
{
    public function __construct(protected CustomerGroupService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.customer-groups.index', compact('items'));
    }

    public function create()
    {
        return view('admin.customer-groups.create');
    }

    public function store(CustomerGroupRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/customer-groups', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('CustomerGroup created successfully.', 'admin.customer-groups.index');
    }

    public function show(CustomerGroup $customerGroup)
    {
        return view('admin.customer-groups.show', ['item' => $customerGroup]);
    }

    public function edit(CustomerGroup $customerGroup)
    {
        return view('admin.customer-groups.edit', ['item' => $customerGroup]);
    }

    public function update(CustomerGroupRequest $request, CustomerGroup $customerGroup)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/customer-groups', 'public');
            }
        }
        $this->service->update($customerGroup, $data);
        return $this->success('CustomerGroup updated successfully.', 'admin.customer-groups.index');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        $this->service->delete($customerGroup);
        return $this->success('CustomerGroup deleted successfully.');
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
