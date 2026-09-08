<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter your email address.',
            'email.unique' => 'An account with this email already exists. Please sign in.',
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'mobile.unique' => 'This mobile number is already registered.',
            'password.required' => 'Please create a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'terms.accepted' => 'Please agree to the Terms & Conditions and Privacy Policy.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'mobile' => preg_replace('/\D+/', '', (string) $this->input('mobile')),
        ]);
    }
}
