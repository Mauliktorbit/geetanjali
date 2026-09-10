<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = is_object($customer) ? $customer->id : $customer;
        $userId = is_object($customer) ? $customer->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => indian_mobile_rules(false),
            'password' => ['nullable', 'string', 'min:8'],
            'is_blocked' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the customer name.',
            'email.required' => 'Please enter the customer email.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already used.',
            'password.min' => 'Password must be at least 8 characters.',
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'phone' => indian_mobile($this->input('phone')),
        ];

        if ($this->has('is_blocked')) {
            $merge['is_blocked'] = $this->boolean('is_blocked');
        }

        $this->merge($merge);
    }
}
