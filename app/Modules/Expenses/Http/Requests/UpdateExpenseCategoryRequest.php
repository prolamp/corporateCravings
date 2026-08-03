<?php

namespace App\Modules\Expenses\Http\Requests;

use App\Modules\Expenses\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('expenses.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ExpenseCategory $category */
        $category = $this->route('expense_category');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('expense_categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }
}
