<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends AdminController
{
    public function __construct(protected BlogService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.blogs.index', compact('items'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(BlogRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/blogs', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Blog created successfully.', 'admin.blogs.index');
    }

    public function show(Blog $blog)
    {
        return view('admin.blogs.show', ['item' => $blog]);
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', ['item' => $blog]);
    }

    public function update(BlogRequest $request, Blog $blog)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/blogs', 'public');
            }
        }
        $this->service->update($blog, $data);
        return $this->success('Blog updated successfully.', 'admin.blogs.index');
    }

    public function destroy(Blog $blog)
    {
        $this->service->delete($blog);
        return $this->success('Blog deleted successfully.');
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
