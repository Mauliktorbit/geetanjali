<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', function (string $attribute, mixed $value, \Closure $fail): void {
                $exists = Category::query()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $value))])
                    ->when($this->categoryId(), fn ($q, $id) => $q->whereKeyNot($id))
                    ->exists();

                if ($exists) {
                    $fail('A category with this name already exists.');
                }
            }],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a category name.',
            'image.image' => 'Please upload a valid image file.',
            'image.max' => 'The image must be 4 MB or smaller.',
        ];
    }

    private function categoryId(): ?int
    {
        $category = $this->route('category');

        if ($category instanceof Category) {
            return (int) $category->id;
        }

        $id = (int) $category;

        return $id > 0 ? $id : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
