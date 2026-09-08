<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends AdminController
{
    public function __construct(protected MenuService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        return view('admin.menus.index', compact('items'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(MenuRequest $request)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/menus', 'public');
            }
        }
        $this->service->create($data);
        return $this->success('Menu created successfully.', 'admin.menus.index');
    }

    public function show(Menu $menu)
    {
        return view('admin.menus.show', ['item' => $menu]);
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', ['item' => $menu]);
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        $data = $request->validated();
        foreach (['logo','image','banner','avatar','attachment','og_image','mobile_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('uploads/menus', 'public');
            }
        }
        $this->service->update($menu, $data);
        return $this->success('Menu updated successfully.', 'admin.menus.index');
    }

    public function destroy(Menu $menu)
    {
        $this->service->delete($menu);
        return $this->success('Menu deleted successfully.');
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
