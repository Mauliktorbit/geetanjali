<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends AdminController
{
    public function index(Menu $menu)
    {
        $items = $menu->items()->with('children')->whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.menu-items.index', compact('menu', 'items'));
    }

    public function store(Request $request, Menu $menu)
    {
        $data = $this->validated($request);
        $data['menu_id'] = $menu->id;
        $data['sort_order'] = $data['sort_order'] ?? ((int) $menu->allItems()->max('sort_order') + 1);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/menu-items', 'public');
        }

        MenuItem::create($data);

        return $this->success('Menu item created.');
    }

    public function update(Request $request, Menu $menu, MenuItem $menuItem)
    {
        abort_unless($menuItem->menu_id === $menu->id, 404);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/menu-items', 'public');
        }

        $menuItem->update($data);

        return $this->success('Menu item updated.');
    }

    public function destroy(Menu $menu, MenuItem $menuItem)
    {
        abort_unless($menuItem->menu_id === $menu->id, 404);
        $menuItem->children()->delete();
        $menuItem->delete();

        return $this->success('Menu item deleted.');
    }

    public function reorder(Request $request, Menu $menu)
    {
        $order = $request->input('order');
        if (is_string($order)) {
            $order = json_decode($order, true) ?: [];
            $request->merge(['order' => $order]);
        }

        $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer'],
            'order.*.sort_order' => ['required', 'integer'],
            'order.*.parent_id' => ['nullable', 'integer'],
        ]);

        foreach ($request->input('order') as $row) {
            MenuItem::where('menu_id', $menu->id)
                ->where('id', $row['id'])
                ->update([
                    'sort_order' => $row['sort_order'],
                    'parent_id' => $row['parent_id'] ?? null,
                ]);
        }

        return $this->success('Menu reordered.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
