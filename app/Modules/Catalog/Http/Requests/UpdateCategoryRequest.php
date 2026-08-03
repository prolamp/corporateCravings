<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Catalog\Models\Category;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
