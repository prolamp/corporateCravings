<?php

namespace App\Modules\Expenses\Http\Requests;

use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('expenses.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:32'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('expense'))],
            'receipt_image' => ['nullable', 'image', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => $this->filled('description') ? $this->input('description') : null,
            'unit' => $this->filled('unit') ? $this->input('unit') : null,
        ]);
    }
}
