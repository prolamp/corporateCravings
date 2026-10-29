<?php

namespace App\Modules\Packages\Http\Requests;

use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('packages.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160', 'unique:packages,title'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'show_on_website' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image' => ['nullable', 'image', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => ['required', 'integer', 'exists:dishes,id', 'distinct'],
            'items.*.dish_variation_id' => ['nullable', 'integer', 'exists:dish_variations,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_on_website' => filter_var(
                $this->input('show_on_website', true),
                FILTER_VALIDATE_BOOLEAN,
            ),
        ]);

        if ($this->has('items') && is_string($this->input('items'))) {
            $decoded = json_decode($this->input('items'), true);
            if (is_array($decoded)) {
                $this->merge(['items' => $decoded]);
            }
        }
    }
}
