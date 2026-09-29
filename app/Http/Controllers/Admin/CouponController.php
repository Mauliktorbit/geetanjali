<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CouponRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\StorefrontCatalogService;
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
        return view('admin.coupons.create', $this->formOptions());
    }

    public function store(CouponRequest $request)
    {
        $this->service->save($request->validated());

        return $this->success('Coupon saved.', 'admin.coupons.index');
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['offers', 'offersWithCode']);

        return view('admin.coupons.show', [
            'item' => $coupon,
            'scopeCategories' => Category::query()
                ->whereIn('id', $coupon->includedCategoryIds())
                ->orderBy('name')
                ->pluck('name'),
            'scopeCollections' => Collection::query()
                ->whereIn('id', $coupon->includedCollectionIds())
                ->orderBy('name')
                ->pluck('name'),
            'scopeProducts' => Product::query()
                ->whereIn('id', $coupon->includedProductIds())
                ->orderBy('name')
                ->get(['name', 'sku']),
        ]);
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', array_merge($this->formOptions(), ['item' => $coupon]));
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

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'collections' => StorefrontCatalogService::adminCollections(),
            'products' => Product::query()
                ->where('is_archived', false)
                ->orderBy('name')
                ->get(['id', 'name', 'sku']),
        ];
    }
}
