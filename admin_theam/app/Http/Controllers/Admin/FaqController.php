<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends AdminController
{
    public function __construct(protected FaqService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.faqs.index', compact('items'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(FaqRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/faqs', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Faq created successfully.', 'admin.faqs.index');
    }

    public function show(Faq $faq)
    {
        return view('admin.faqs.show', ['item' => $faq]);
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', ['item' => $faq]);
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/faqs', 'public');
            }
        }
        $this->service->update($faq, $data);
        return $this->success('Faq updated successfully.', 'admin.faqs.index');
    }

    public function destroy(Faq $faq)
    {
        $this->service->delete($faq);
        return $this->success('Faq deleted successfully.');
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
