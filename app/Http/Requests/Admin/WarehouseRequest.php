<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'code' => 'required',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'pincode' => indian_pincode_rules(false),
            'phone' => indian_mobile_rules(false),
            'priority' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
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
        $pincode = digits_only($this->input('pincode'));
        $booleans['phone'] = indian_mobile($this->input('phone'));
        $booleans['pincode'] = $pincode === '' ? null : $pincode;

        $this->merge($booleans);
    }
}
