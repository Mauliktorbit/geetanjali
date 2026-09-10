<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends AdminController
{
    public function index(Request $request)
    {
        $query = User::where('is_staff', true)->with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.staff.index', compact('items'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => indian_mobile($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => indian_mobile_rules(false),
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'allowed_ips' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_staff' => true,
            'is_active' => $request->boolean('is_active', true),
            'allowed_ips' => $this->parseIps($data['allowed_ips'] ?? null),
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $this->success('Staff member created.', 'admin.staff.index');
    }

    public function edit(User $staff)
    {
        abort_unless($staff->is_staff, 404);
        $roles = Role::orderBy('name')->get();

        return view('admin.staff.edit', ['item' => $staff, 'roles' => $roles]);
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->is_staff, 404);

        $request->merge([
            'phone' => indian_mobile($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff->id)],
            'phone' => indian_mobile_rules(false),
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'allowed_ips' => ['nullable', 'string'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'allowed_ips' => $this->parseIps($data['allowed_ips'] ?? null),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $staff->update($payload);
        $staff->syncRoles($data['roles'] ?? []);

        return $this->success('Staff member updated.', 'admin.staff.index');
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->is_staff, 404);

        if ($staff->id === auth()->id()) {
            return $this->error('You cannot delete your own account.');
        }

        $staff->delete();

        return $this->success('Staff member deleted.');
    }

    protected function parseIps(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
