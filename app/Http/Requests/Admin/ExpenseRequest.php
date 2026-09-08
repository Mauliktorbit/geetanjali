<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric',
            'expense_date' => 'required',
            'payment_method' => 'nullable',
            'reference' => 'nullable',
            'notes' => 'nullable',
            'attachment' => 'nullable|file',
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
