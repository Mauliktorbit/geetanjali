<?php

namespace App\Http\Requests\Admin;

use App\Models\Collection;
use Illuminate\Foundation\Http\FormRequest;
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
        $collectionId = $collection instanceof Collection ? $collection->id : $collection;

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('collections', 'slug')->ignore($collectionId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a collection name.',
            'slug.regex' => 'Use lowercase letters, numbers and hyphens only, for example festive-edit.',
            'slug.unique' => 'That URL slug is already used by another collection.',
            'description.max' => 'Details must be 2,000 characters or fewer.',
            'image.image' => 'Please upload a valid image file.',
            'image.max' => 'The image must be 4 MB or smaller.',
            'seo_title.max' => 'SEO title must be 180 characters or fewer.',
            'seo_description.max' => 'SEO description must be 320 characters or fewer.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = strtolower(trim((string) $this->input('slug')));
        $slug = trim($slug, '-');

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => $slug !== '' ? $slug : null,
            'description' => trim((string) $this->input('description')),
            'seo_title' => trim((string) $this->input('seo_title')),
            'seo_description' => trim((string) $this->input('seo_description')),
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }
}
