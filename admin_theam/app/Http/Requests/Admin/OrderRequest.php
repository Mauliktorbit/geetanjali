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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'source' => ['nullable', 'string', 'max:50'],
            'sales_channel' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'shipping_method' => ['nullable', 'string', 'max:100'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_charge' => ['nullable', 'numeric', 'min:0'],
            'cod_charge' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'customer_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'billing_address' => ['nullable', 'array'],
            'billing_address.name' => ['nullable', 'string', 'max:255'],
            'billing_address.line1' => ['nullable', 'string', 'max:255'],
            'billing_address.line2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:100'],
            'billing_address.state' => ['nullable', 'string', 'max:100'],
            'billing_address.country' => ['nullable', 'string', 'max:100'],
            'billing_address.pincode' => ['nullable', 'string', 'max:20'],
            'billing_address.phone' => ['nullable', 'string', 'max:30'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.name' => ['nullable', 'string', 'max:255'],
            'shipping_address.line1' => ['nullable', 'string', 'max:255'],
            'shipping_address.line2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['nullable', 'string', 'max:100'],
            'shipping_address.state' => ['nullable', 'string', 'max:100'],
            'shipping_address.country' => ['nullable', 'string', 'max:100'],
            'shipping_address.pincode' => ['nullable', 'string', 'max:20'],
            'shipping_address.phone' => ['nullable', 'string', 'max:30'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'shipping_pincode' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
