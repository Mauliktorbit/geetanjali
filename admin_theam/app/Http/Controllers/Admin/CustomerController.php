<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerNote;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends AdminController
{
    public function __construct(protected CustomerService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        $groups = CustomerGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.customers.index', compact('items', 'groups'));
    }

    public function create()
    {
        $groups = CustomerGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.customers.create', compact('groups'));
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->service->create($request->validated());

        return $this->success('Customer created successfully.', 'admin.customers.show', [$customer]);
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'group', 'addresses', 'user',
            'orders' => fn ($q) => $q->latest()->limit(20),
            'walletTransactions' => fn ($q) => $q->latest()->limit(20),
            'rewardPointTransactions' => fn ($q) => $q->latest()->limit(20),
            'wishlists.product',
            'customerNotes.user',
        ]);
        $groups = CustomerGroup::orderBy('name')->get(['id', 'name']);
        $customers = Customer::where('id', '!=', $customer->id)->orderBy('name')->limit(200)->get(['id', 'name', 'email']);

        return view('admin.customers.show', [
            'item' => $customer,
            'groups' => $groups,
            'customers' => $customers,
        ]);
    }

    public function edit(Customer $customer)
    {
        $groups = CustomerGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.customers.edit', ['item' => $customer, 'groups' => $groups]);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->service->update($customer, $request->validated());

        return $this->success('Customer updated successfully.', 'admin.customers.show', [$customer]);
    }

    public function destroy(Customer $customer)
    {
        $this->service->delete($customer);

        return $this->success('Customer deleted successfully.', 'admin.customers.index');
    }

    public function block(Request $request, Customer $customer)
    {
        $this->service->block($customer, $request->input('reason'));

        return $this->success('Customer blocked.');
    }

    public function unblock(Customer $customer)
    {
        $this->service->unblock($customer);

        return $this->success('Customer unblocked.');
    }

    public function resetPassword(Request $request, Customer $customer)
    {
        $password = $this->service->resetPassword($customer, $request->input('password'));

        return $this->success('Password reset. Temporary password: ' . $password);
    }

    public function assignGroup(Request $request, Customer $customer)
    {
        $request->validate(['customer_group_id' => ['nullable', 'exists:customer_groups,id']]);
        $customer->update(['customer_group_id' => $request->input('customer_group_id')]);

        return $this->success('Customer group updated.');
    }

    public function addStoreCredit(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->addStoreCredit(
            $customer,
            (float) $request->input('amount'),
            $request->input('reason', 'Store credit')
        );

        return $this->success('Store credit updated.');
    }

    public function addRewardPoints(Request $request, Customer $customer)
    {
        $request->validate([
            'points' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->addRewardPoints(
            $customer,
            (int) $request->input('points'),
            $request->input('reason', 'Reward points')
        );

        return $this->success('Reward points updated.');
    }

    public function addNote(Request $request, Customer $customer)
    {
        $request->validate(['note' => ['required', 'string']]);

        CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'note' => $request->input('note'),
        ]);

        return $this->success('Note added.');
    }

    public function merge(Request $request, Customer $customer)
    {
        $request->validate(['secondary_id' => ['required', 'exists:customers,id', 'different:' . $customer->id]]);
        $secondary = Customer::findOrFail($request->input('secondary_id'));
        $this->service->mergeCustomers($customer, $secondary);

        return $this->success('Customers merged.', 'admin.customers.show', [$customer]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'customers-' . now()->format('Ymd-His') . '.csv';
        $customers = Customer::with('group')->latest()->get();

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Group', 'Orders', 'Spent', 'Wallet', 'Points', 'Blocked']);
            foreach ($customers as $c) {
                fputcsv($out, [
                    $c->id, $c->name, $c->email, $c->phone, $c->group?->name,
                    $c->total_orders, $c->total_spent, $c->wallet_balance, $c->reward_points,
                    $c->is_blocked ? 'Yes' : 'No',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
