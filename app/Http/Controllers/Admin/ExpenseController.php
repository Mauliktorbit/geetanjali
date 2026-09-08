<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends AdminController
{
    public function __construct(protected ExpenseService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.expenses.index', compact('items'));
    }

    public function create()
    {
        return view('admin.expenses.create');
    }

    public function store(ExpenseRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/expenses', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Expense created successfully.', 'admin.expenses.index');
    }

    public function show(Expense $expense)
    {
        return view('admin.expenses.show', ['item' => $expense]);
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.edit', ['item' => $expense]);
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/expenses', 'public');
            }
        }
        $this->service->update($expense, $data);
        return $this->success('Expense updated successfully.', 'admin.expenses.index');
    }

    public function destroy(Expense $expense)
    {
        $this->service->delete($expense);
        return $this->success('Expense deleted successfully.');
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
