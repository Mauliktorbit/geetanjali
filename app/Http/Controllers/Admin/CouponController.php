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
        $items = $this->service->paginate($request->only(['search', 'status']));

        return view('admin.coupons.index', compact('items'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request)
    {
        $this->service->save($request->validated());

        return $this->success('Coupon saved.', 'admin.coupons.index');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', [
            'item' => $coupon->load(['offers', 'offersWithCode']),
        ]);
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', ['item' => $coupon]);
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $this->service->save($request->validated(), $coupon);

        return $this->success('Coupon updated.', 'admin.coupons.index');
    }

    public function destroy(Coupon $coupon)
    {
        $this->service->delete($coupon);

        return $this->success('Coupon removed.', 'admin.coupons.index');
    }
}
