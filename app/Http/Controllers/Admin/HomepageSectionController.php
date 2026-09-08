<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\HomepageSectionRequest;
use App\Models\HomepageSection;
use App\Services\HomepageSectionService;
use Illuminate\Http\Request;

class HomepageSectionController extends AdminController
{
    public function __construct(protected HomepageSectionService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.homepage-sections.index', compact('items'));
    }

    public function create()
    {
        return view('admin.homepage-sections.create');
    }

    public function store(HomepageSectionRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/homepage-sections', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('HomepageSection created successfully.', 'admin.homepage-sections.index');
    }

    public function show(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.show', ['item' => $homepageSection]);
    }

    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.edit', ['item' => $homepageSection]);
    }

    public function update(HomepageSectionRequest $request, HomepageSection $homepageSection)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/homepage-sections', 'public');
            }
        }
        $this->service->update($homepageSection, $data);
        return $this->success('HomepageSection updated successfully.', 'admin.homepage-sections.index');
    }

    public function destroy(HomepageSection $homepageSection)
    {
        $this->service->delete($homepageSection);
        return $this->success('HomepageSection deleted successfully.');
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
