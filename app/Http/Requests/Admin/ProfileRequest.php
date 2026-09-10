<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        $changingPassword = $this->isChangingPassword();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => indian_mobile_rules(false),
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($changingPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already used.',
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
            'current_password.required' => 'Enter your current password to set a new one.',
            'current_password.current_password' => 'The current password is not correct.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password and confirmation do not match.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $newPassword = trim((string) $this->input('new_password', ''));
        $currentPassword = trim((string) $this->input('current_password', ''));

        $this->merge([
            'phone' => indian_mobile($this->input('phone')),
            'new_password' => $newPassword === '' ? null : $newPassword,
            'new_password_confirmation' => $newPassword === '' ? null : $this->input('new_password_confirmation'),
            'current_password' => $newPassword === '' ? null : ($currentPassword === '' ? null : $currentPassword),
        ]);
    }

    private function isChangingPassword(): bool
    {
        return filled($this->input('new_password'));
    }
}
