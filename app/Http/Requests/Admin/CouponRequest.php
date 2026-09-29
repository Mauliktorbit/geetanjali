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
            'maximum_discount' => ['nullable', 'numeric', 'min:0.01'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'customer_audience' => ['required', 'in:all,new,returning'],
            'new_customers_only' => ['boolean'],
            'existing_customers_only' => ['boolean'],
            'exclude_sale_items' => ['boolean'],
            'included_categories' => ['nullable', 'array'],
            'included_categories.*' => ['integer', Rule::exists('categories', 'id')],
            'included_collections' => ['nullable', 'array'],
            'included_collections.*' => ['integer', Rule::exists('collections', 'id')],
            'included_products' => ['nullable', 'array'],
            'included_products.*' => ['integer', Rule::exists('products', 'id')],
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
            'starts_at.regex' => 'Choose a valid start date.',
            'ends_at.regex' => 'Choose a valid end date.',
            'ends_at.after_or_equal' => 'End date must be on or after the start date.',
            'maximum_discount.min' => 'Maximum discount must be greater than 0, or leave blank for no cap.',
            'customer_audience.in' => 'Choose all customers, new customers, or returning customers.',
            'usage_limit.min' => 'Total uses must be at least 1, or leave blank for unlimited.',
            'per_customer_limit.min' => 'Uses per customer must be at least 1, or leave blank for unlimited.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $audience = (string) $this->input('customer_audience', '');
        if (! in_array($audience, ['all', 'new', 'returning'], true)) {
            $audience = $this->boolean('new_customers_only')
                ? 'new'
                : ($this->boolean('existing_customers_only') ? 'returning' : 'all');
        }

        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'is_active' => $this->boolean('is_active'),
            'customer_audience' => $audience,
            'new_customers_only' => $audience === 'new',
            'existing_customers_only' => $audience === 'returning',
            'exclude_sale_items' => $this->boolean('exclude_sale_items'),
            'included_categories' => $this->idList('included_categories'),
            'included_collections' => $this->idList('included_collections'),
            'included_products' => $this->idList('included_products'),
            'minimum_cart' => $this->filled('minimum_cart') ? $this->input('minimum_cart') : null,
            'maximum_discount' => $this->filled('maximum_discount') ? $this->input('maximum_discount') : null,
            'usage_limit' => $this->filled('usage_limit') ? $this->input('usage_limit') : null,
            'per_customer_limit' => $this->filled('per_customer_limit') ? $this->input('per_customer_limit') : null,
            'starts_at' => $this->filled('starts_at') ? trim((string) $this->input('starts_at')) : null,
            'ends_at' => $this->filled('ends_at') ? trim((string) $this->input('ends_at')) : null,
        ]);
    }

    /**
     * @return list<int>
     */
    private function idList(string $key): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $this->input($key, [])),
            static fn (int $id) => $id > 0
        )));
    }
}
