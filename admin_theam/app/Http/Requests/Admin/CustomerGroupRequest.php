<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'slug' => 'required',
            'discount_percent' => 'nullable|numeric',
            'payment_terms_days' => 'nullable|numeric',
            'moq' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
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
