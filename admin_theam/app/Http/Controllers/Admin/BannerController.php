<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;

class BannerController extends AdminController
{
    public function __construct(protected BannerService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.banners.index', compact('items'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/banners', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Banner created successfully.', 'admin.banners.index');
    }

    public function show(Banner $banner)
    {
        return view('admin.banners.show', ['item' => $banner]);
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', ['item' => $banner]);
    }

    public function update(BannerRequest $request, Banner $banner)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/banners', 'public');
            }
        }
        $this->service->update($banner, $data);
        return $this->success('Banner updated successfully.', 'admin.banners.index');
    }

    public function destroy(Banner $banner)
    {
        $this->service->delete($banner);
        return $this->success('Banner deleted successfully.');
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
