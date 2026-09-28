<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InventoryBulkAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'stock_to_add' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Select at least one product.',
            'ids.min' => 'Select at least one product.',
            'stock_to_add.required' => 'Please enter how many pieces to add.',
            'stock_to_add.min' => 'Stock to add must be at least 1.',
        ];
    }
}
