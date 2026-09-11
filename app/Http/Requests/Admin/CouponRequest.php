<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('coupons', 'code')->ignore($coupon?->id)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:120'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => [
                'required',
                'numeric',
                'min:0.01',
                Rule::when($this->input('discount_type') === 'percent', ['max:100']),
            ],
            'starts_at' => dmy_date_rules(),
            'ends_at' => dmy_date_rules(afterOrEqual: 'starts_at'),
            'minimum_cart' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Enter a coupon code.',
            'code.regex' => 'Use only letters, numbers, dash or underscore.',
            'code.unique' => 'This coupon code is already in use.',
            'name.required' => 'Enter a name for this offer.',
            'discount_type.in' => 'Choose percent or a fixed amount.',
            'discount_value.min' => 'Enter a discount greater than 0.',
            'discount_value.max' => 'Percent discount cannot be more than 100.',
            'starts_at.regex' => 'Enter the start date as DD/MM/YYYY, for example 26/12/2026.',
            'ends_at.regex' => 'Enter the end date as DD/MM/YYYY, for example 26/12/2026.',
            'ends_at.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'is_active' => $this->boolean('is_active'),
            'minimum_cart' => $this->filled('minimum_cart') ? $this->input('minimum_cart') : null,
            'starts_at' => $this->filled('starts_at') ? trim((string) $this->input('starts_at')) : null,
            'ends_at' => $this->filled('ends_at') ? trim((string) $this->input('ends_at')) : null,
        ]);
    }
}
