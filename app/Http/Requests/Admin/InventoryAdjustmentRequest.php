<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'stock_to_add' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please choose a product.',
            'stock_to_add.required' => 'Please enter how many pieces to add.',
            'stock_to_add.min' => 'Stock to add must be at least 1.',
        ];
    }
}
