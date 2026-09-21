<?php

namespace App\Http\Requests\Admin;

use App\Models\Offer;
use App\Models\OfferCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $offer = $this->route('offer');
        $isCreate = ! $offer instanceof Offer;
        $slugs = OfferCategory::query()->pluck('slug')->all();

        return [
            'category' => ['required', Rule::in(array_merge($slugs, ['__new__']))],
            'new_category' => ['required_if:category,__new__', 'nullable', 'string', 'max:80'],
            'label' => ['required', Rule::in(Offer::cardLabels())],
            'discount_display' => ['required', 'string', 'max:20'],
            'discount_suffix' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:120'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'theme' => ['required', Rule::in(array_keys(Offer::themes()))],
            'starts_at' => dmy_date_rules(),
            'ends_at' => dmy_date_rules(),
            'image' => [
                $isCreate ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'is_active' => ['boolean'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Choose a category.',
            'category.in' => 'Choose a valid category, or add a new one.',
            'new_category.required_if' => 'Enter a name for the new category.',
            'label.required' => 'Choose how the offer is labelled, for example Flat or Extra.',
            'title.required' => 'Enter the headline customers will see.',
            'discount_display.required' => 'Enter the discount, for example 10% or ₹500.',
            'discount_suffix.required' => 'Enter the word next to the discount, usually Off.',
            'theme.required' => 'Choose a card style.',
            'image.required' => 'Upload a photo for this offer.',
            'image.image' => 'Please upload a valid image file.',
            'image.max' => 'The image must be 4 MB or smaller.',
            'starts_at.regex' => 'Enter the start date as DD/MM/YYYY.',
            'ends_at.regex' => 'Enter the end date as DD/MM/YYYY, for example 26/12/2026.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'discount_display' => trim((string) $this->input('discount_display')),
            'discount_suffix' => trim((string) $this->input('discount_suffix', 'Off')) ?: 'Off',
            'new_category' => trim((string) $this->input('new_category')),
            'minimum_order' => $this->filled('minimum_order') ? $this->input('minimum_order') : null,
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'starts_at' => $this->filled('starts_at') ? trim((string) $this->input('starts_at')) : null,
            'ends_at' => $this->filled('ends_at') ? trim((string) $this->input('ends_at')) : null,
        ]);
    }
}
