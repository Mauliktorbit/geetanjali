<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PolicyPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'intro' => ['required', 'string', 'max:2000'],
            'sections' => ['nullable', 'array'],
            'sections.*.heading' => ['nullable', 'string', 'max:160'],
            'sections.*.body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasSection = false;
            foreach ((array) $this->input('sections', []) as $section) {
                if (trim((string) ($section['heading'] ?? '')) !== '' || trim((string) ($section['body'] ?? '')) !== '') {
                    $hasSection = true;
                    break;
                }
            }
            if (! $hasSection) {
                $validator->errors()->add('sections', 'Add at least one section with a heading or text.');
            }
        });
    }
}
