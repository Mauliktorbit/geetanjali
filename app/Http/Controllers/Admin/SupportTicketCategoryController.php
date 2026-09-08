<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SupportTicketCategoryRequest;
use App\Models\SupportTicketCategory;
use App\Services\SupportTicketCategoryService;
use Illuminate\Http\Request;

class SupportTicketCategoryController extends AdminController
{
    public function __construct(protected SupportTicketCategoryService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.support-ticket-categories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.support-ticket-categories.create');
    }

    public function store(SupportTicketCategoryRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/support-ticket-categories', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('SupportTicketCategory created successfully.', 'admin.support-ticket-categories.index');
    }

    public function show(SupportTicketCategory $supportTicketCategory)
    {
        return view('admin.support-ticket-categories.show', ['item' => $supportTicketCategory]);
    }

    public function edit(SupportTicketCategory $supportTicketCategory)
    {
        return view('admin.support-ticket-categories.edit', ['item' => $supportTicketCategory]);
    }

    public function update(SupportTicketCategoryRequest $request, SupportTicketCategory $supportTicketCategory)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/support-ticket-categories', 'public');
            }
        }
        $this->service->update($supportTicketCategory, $data);
        return $this->success('SupportTicketCategory updated successfully.', 'admin.support-ticket-categories.index');
    }

    public function destroy(SupportTicketCategory $supportTicketCategory)
    {
        $this->service->delete($supportTicketCategory);
        return $this->success('SupportTicketCategory deleted successfully.');
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
