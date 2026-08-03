<?php

namespace App\Modules\Addons\Http\Requests;

use App\Modules\Addons\Models\Addon;
use App\Support\Enums\AddonType;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('addons.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Addon $addon */
        $addon = $this->route('addon');

        return [
            'title' => [
                'required',
                'string',
                'max:160',
                Rule::unique('addons', 'title')->ignore($addon->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', Rule::in(AddonType::values())],
            'price' => ['required', 'numeric', 'min:0'],
            'is_global' => ['required', 'boolean'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
            'dish_ids' => ['nullable', 'array'],
            'dish_ids.*' => ['integer', 'exists:dishes,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_global' => filter_var($this->input('is_global', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        if ($this->has('dish_ids') && is_string($this->input('dish_ids'))) {
            $decoded = json_decode($this->input('dish_ids'), true);
            if (is_array($decoded)) {
                $this->merge(['dish_ids' => $decoded]);
            }
        }
    }
}
