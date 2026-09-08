<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SeoRedirectRequest;
use App\Models\SeoRedirect;
use App\Services\SeoRedirectService;
use Illuminate\Http\Request;

class SeoRedirectController extends AdminController
{
    public function __construct(protected SeoRedirectService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.seo-redirects.index', compact('items'));
    }

    public function create()
    {
        return view('admin.seo-redirects.create');
    }

    public function store(SeoRedirectRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/seo-redirects', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('SeoRedirect created successfully.', 'admin.seo-redirects.index');
    }

    public function show(SeoRedirect $seoRedirect)
    {
        return view('admin.seo-redirects.show', ['item' => $seoRedirect]);
    }

    public function edit(SeoRedirect $seoRedirect)
    {
        return view('admin.seo-redirects.edit', ['item' => $seoRedirect]);
    }

    public function update(SeoRedirectRequest $request, SeoRedirect $seoRedirect)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/seo-redirects', 'public');
            }
        }
        $this->service->update($seoRedirect, $data);
        return $this->success('SeoRedirect updated successfully.', 'admin.seo-redirects.index');
    }

    public function destroy(SeoRedirect $seoRedirect)
    {
        $this->service->delete($seoRedirect);
        return $this->success('SeoRedirect deleted successfully.');
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
