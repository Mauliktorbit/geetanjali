<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AttributeRequest;
use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\Request;

class AttributeController extends AdminController
{
    public function __construct(protected AttributeService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.attributes.index', compact('items'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(AttributeRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/attributes', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Attribute created successfully.', 'admin.attributes.index');
    }

    public function show(Attribute $attribute)
    {
        return view('admin.attributes.show', ['item' => $attribute]);
    }

    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', ['item' => $attribute]);
    }

    public function update(AttributeRequest $request, Attribute $attribute)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/attributes', 'public');
            }
        }
        $this->service->update($attribute, $data);
        return $this->success('Attribute updated successfully.', 'admin.attributes.index');
    }

    public function destroy(Attribute $attribute)
    {
        $this->service->delete($attribute);
        return $this->success('Attribute deleted successfully.');
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
