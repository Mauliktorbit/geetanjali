<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required',
            'name' => 'required',
            'discount_type' => 'required',
            'discount_value' => 'required|numeric',
            'starts_at' => 'nullable',
            'ends_at' => 'nullable',
            'usage_limit' => 'nullable|numeric',
            'per_customer_limit' => 'nullable|numeric',
            'minimum_cart' => 'nullable|numeric',
            'maximum_discount' => 'nullable|numeric',
            'new_customers_only' => 'nullable|boolean',
            'is_stackable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleans = [];
        foreach ($this->rules() as $key => $rule) {
            if (is_string($rule) && str_contains($rule, 'boolean')) {
                $booleans[$key] = $this->boolean($key);
            }
        }
        if ($booleans) {
            $this->merge($booleans);
        }
    }
}
