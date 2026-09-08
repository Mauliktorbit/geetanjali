<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends AdminController
{
    public function __construct(protected TagService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.tags.index', compact('items'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(TagRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/tags', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Tag created successfully.', 'admin.tags.index');
    }

    public function show(Tag $tag)
    {
        return view('admin.tags.show', ['item' => $tag]);
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', ['item' => $tag]);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/tags', 'public');
            }
        }
        $this->service->update($tag, $data);
        return $this->success('Tag updated successfully.', 'admin.tags.index');
    }

    public function destroy(Tag $tag)
    {
        $this->service->delete($tag);
        return $this->success('Tag deleted successfully.');
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
