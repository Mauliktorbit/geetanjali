<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends AdminController
{
    public function __construct(protected CustomerService $service) {}

    public function index(Request $request)
    {
        $this->service->collapseDuplicates();
        $items = $this->service->paginate($request->only(['search', 'status']));

        return view('admin.customers.index', compact('items'));
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'addresses',
            'orders' => fn ($q) => $q->with('items')->latest()->limit(20),
        ]);

        return view('admin.customers.show', ['item' => $customer]);
    }

    public function toggle(Customer $customer)
    {
        $active = $customer->is_blocked;
        $this->service->setActive($customer, $active);

        return $this->success($active
            ? $customer->name.' is now active.'
            : $customer->name.' is now inactive and cannot log in.');
    }
}
