<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends AdminController
{
    public function index()
    {
        $items = Role::withCount('permissions', 'users')->orderBy('name')->paginate(20);

        return view('admin.roles.index', compact('items'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->success('Role created.', 'admin.roles.index');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'general';
        });

        return view('admin.roles.edit', [
            'item' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->success('Role updated.', 'admin.roles.index');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return $this->error('Cannot delete super-admin role.');
        }

        $role->delete();

        return $this->success('Role deleted.');
    }
}
