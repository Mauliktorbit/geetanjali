<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends AdminController
{
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'item' => Auth::user(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        if (filled($data['new_password'] ?? null)) {
            $payload['password'] = $data['new_password'];
        }

        $user->update($payload);

        return $this->success('Your profile has been saved.', 'admin.profile.edit');
    }
}
