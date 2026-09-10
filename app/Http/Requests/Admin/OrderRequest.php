<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => indian_mobile_rules(true),
            'shipping_address.line1' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_pincode' => indian_pincode_rules(false),
            'payment_status' => ['required', 'in:pending,paid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Please enter the customer name.',
            'customer_phone.required' => 'Please enter the customer phone number.',
            'customer_phone.regex' => 'Enter a valid 10-digit mobile number.',
            'shipping_pincode.regex' => 'Enter a valid 6-digit pincode.',
            'items.required' => 'Please add at least one product.',
            'items.*.product_id.required' => 'Please choose a product.',
            'items.*.quantity.required' => 'Please enter quantity.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $pincode = digits_only($this->input('shipping_pincode'));

        $this->merge([
            'customer_phone' => indian_mobile($this->input('customer_phone')),
            'shipping_pincode' => $pincode === '' ? null : $pincode,
        ]);
    }
}
