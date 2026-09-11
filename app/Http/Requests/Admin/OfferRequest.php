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
            'title' => ['required', 'string', 'max:120'],
            'discount_display' => ['required', 'string', 'max:20'],
            'image' => [
                $isCreate ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'ends_at' => dmy_date_rules(),
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Choose a category.',
            'category.in' => 'Choose a valid category, or add a new one.',
            'new_category.required_if' => 'Enter a name for the new category.',
            'title.required' => 'Enter the headline customers will see.',
            'discount_display.required' => 'Enter the discount, for example 10%.',
            'image.required' => 'Upload a photo for this offer.',
            'image.image' => 'Please upload a valid image file.',
            'image.max' => 'The image must be 4 MB or smaller.',
            'ends_at.regex' => 'Enter the date as DD/MM/YYYY, for example 26/12/2026.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'discount_display' => trim((string) $this->input('discount_display')),
            'new_category' => trim((string) $this->input('new_category')),
            'is_active' => $this->boolean('is_active'),
            'ends_at' => $this->filled('ends_at') ? trim((string) $this->input('ends_at')) : null,
        ]);
    }
}
