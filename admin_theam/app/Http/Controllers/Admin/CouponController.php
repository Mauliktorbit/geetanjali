<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends AdminController
{
    public function __construct(protected CouponService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.coupons.index', compact('items'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/coupons', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Coupon created successfully.', 'admin.coupons.index');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', ['item' => $coupon]);
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', ['item' => $coupon]);
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/coupons', 'public');
            }
        }
        $this->service->update($coupon, $data);
        return $this->success('Coupon updated successfully.', 'admin.coupons.index');
    }

    public function destroy(Coupon $coupon)
    {
        $this->service->delete($coupon);
        return $this->success('Coupon deleted successfully.');
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
