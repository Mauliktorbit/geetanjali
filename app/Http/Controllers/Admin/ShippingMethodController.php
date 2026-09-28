<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Services\ShippingMethodService;
use Illuminate\Http\Request;

class ShippingMethodController extends AdminController
{
    public function __construct(protected ShippingMethodService $service) {}

    public function index(Request $request)
    {
        $this->service->ensureDefaults();
        $items = $this->service->paginate($request->all());

        return view('admin.shipping-methods.index', compact('items'));
    }

    public function create()
    {
        return view('admin.shipping-methods.create');
    }

    public function store(ShippingMethodRequest $request)
    {
        $this->service->create($this->prepareRule($request->validated()));

        return $this->success('Shipping rule saved.', 'admin.shipping-methods.index');
    }

    public function show(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping-methods.show', ['item' => $shippingMethod]);
    }

    public function edit(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping-methods.edit', ['item' => $shippingMethod]);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $shippingMethod)
    {
        $this->service->update($shippingMethod, $this->prepareRule($request->validated(), $shippingMethod));

        return $this->success('Shipping rule updated.', 'admin.shipping-methods.index');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $this->service->delete($shippingMethod);

        return $this->success('Shipping rule deleted.');
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');
        if (! $ids) {
            return $this->error('Please select at least one shipping rule.');
        }
        if ($action === 'delete') {
            $this->service->bulkDelete($ids);

            return $this->success('Selected shipping rules deleted.');
        }
        if ($action === 'activate') {
            $this->service->bulkUpdate($ids, ['is_active' => true]);

            return $this->success('Selected shipping rules activated.');
        }
        if ($action === 'deactivate') {
            $this->service->bulkUpdate($ids, ['is_active' => false]);

            return $this->success('Selected shipping rules deactivated.');
        }

        return $this->error('Invalid bulk action.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareRule(array $data, ?ShippingMethod $existing = null): array
    {
        $data['type'] = $data['type'] ?: 'flat';
        $data['rate'] = (float) ($data['rate'] ?? 0);
        $data['cod_available'] = true;
        $data['cod_charges'] = $data['cod_charges'] ?? 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['estimated_delivery'] = filled($data['estimated_delivery'] ?? null)
            ? $data['estimated_delivery']
            : '3–5 business days';

        if (empty($data['code'])) {
            $data['code'] = $this->service->uniqueCode((string) $data['name'], $existing?->id);
        }

        return $data;
    }
}
