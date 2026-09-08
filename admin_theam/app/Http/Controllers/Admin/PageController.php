<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends AdminController
{
    public function __construct(protected PageService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.pages.index', compact('items'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(PageRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/pages', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Page created successfully.', 'admin.pages.index');
    }

    public function show(Page $page)
    {
        return view('admin.pages.show', ['item' => $page]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['item' => $page]);
    }

    public function update(PageRequest $request, Page $page)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/pages', 'public');
            }
        }
        $this->service->update($page, $data);
        return $this->success('Page updated successfully.', 'admin.pages.index');
    }

    public function destroy(Page $page)
    {
        $this->service->delete($page);
        return $this->success('Page deleted successfully.');
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
