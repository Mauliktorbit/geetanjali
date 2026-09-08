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
        $customerId = $this->route('customer')?->id ?? $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'acquisition_source' => ['nullable', 'string', 'max:100'],
            'password' => [$this->isMethod('post') ? 'nullable' : 'nullable', 'string', 'min:8'],
            'is_verified' => ['nullable', 'boolean'],
            'is_blocked' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_verified' => $this->boolean('is_verified'),
            'is_blocked' => $this->boolean('is_blocked'),
        ]);
    }
}
