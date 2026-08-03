<?php

namespace App\Modules\Packages\Http\Requests;

use App\Modules\Packages\Models\Package;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
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
        /** @var Package $package */
        $package = $this->route('package');

        return [
            'title' => [
                'required',
                'string',
                'max:160',
                Rule::unique('packages', 'title')->ignore($package->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => ['required', 'integer', 'exists:dishes,id', 'distinct'],
            'items.*.dish_variation_id' => ['nullable', 'integer', 'exists:dish_variations,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('items') && is_string($this->input('items'))) {
            $decoded = json_decode($this->input('items'), true);
            if (is_array($decoded)) {
                $this->merge(['items' => $decoded]);
            }
        }
    }
}
