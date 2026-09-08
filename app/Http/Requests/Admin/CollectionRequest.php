<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $collection = $this->route('collection');

        return [
            'name' => 'required|string|max:160',
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('collections', 'slug')->ignore($collection?->id),
            ],
            'type' => 'required|string|max:50',
            'image' => 'nullable|file|image',
            'banner' => 'nullable|file|image',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:180',
            'seo_description' => 'nullable|string',
            'sort_order' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug') ?: Str::slug((string) $this->input('name'));

        $this->merge([
            'slug' => $slug,
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
