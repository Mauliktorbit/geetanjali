<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $methodId = $this->route('shipping_method')?->id ?? $this->route('shipping_method');

        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('shipping_methods', 'code')->ignore($methodId)],
            'type' => ['nullable', 'string', 'max:40'],
            'rate' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter a name customers will see at checkout.',
            'rate.required' => 'Enter the shipping charge, or 0 for free delivery.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'type' => $this->input('type') ?: 'flat',
            'min_order_amount' => $this->filled('min_order_amount') ? $this->input('min_order_amount') : null,
            'free_shipping_threshold' => $this->filled('free_shipping_threshold') ? $this->input('free_shipping_threshold') : null,
        ]);
    }
}
