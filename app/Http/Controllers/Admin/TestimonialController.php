<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\Request;

class TestimonialController extends AdminController
{
    public function __construct(protected TestimonialService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.testimonials.index', compact('items'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(TestimonialRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/testimonials', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Testimonial created successfully.', 'admin.testimonials.index');
    }

    public function show(Testimonial $testimonial)
    {
        return view('admin.testimonials.show', ['item' => $testimonial]);
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', ['item' => $testimonial]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/testimonials', 'public');
            }
        }
        $this->service->update($testimonial, $data);
        return $this->success('Testimonial updated successfully.', 'admin.testimonials.index');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->service->delete($testimonial);
        return $this->success('Testimonial deleted successfully.');
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
