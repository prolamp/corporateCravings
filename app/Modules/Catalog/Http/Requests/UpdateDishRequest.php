<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\VariationType;
use App\Support\Enums\VariationUnit;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dishes.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Dish $dish */
        $dish = $this->route('dish');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => [
                'required',
                'string',
                'max:160',
                Rule::unique('dishes', 'title')->ignore($dish->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.id' => ['nullable', 'integer'],
            'variations.*.label' => ['required', 'string', 'max:80'],
            'variations.*.quantity' => ['required', 'numeric', 'gt:0'],
            'variations.*.unit' => ['required', 'string', Rule::in(VariationUnit::values())],
            'variations.*.price' => ['required', 'numeric', 'min:0'],
            'variations.*.type' => ['required', 'string', Rule::in(VariationType::values())],
            'variations.*.status' => ['nullable', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'variations.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('variations') && is_string($this->input('variations'))) {
            $decoded = json_decode($this->input('variations'), true);
            if (is_array($decoded)) {
                $this->merge(['variations' => $decoded]);
            }
        }
    }
}
