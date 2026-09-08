<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required',
            'slug' => 'required',
            'image' => 'nullable|file',
            'excerpt' => 'nullable',
            'content' => 'nullable',
            'seo_title' => 'nullable',
            'seo_description' => 'nullable',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable',
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
