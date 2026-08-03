<?php

namespace App\Modules\PublicSite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dish_variation_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('addon_ids') && is_string($this->input('addon_ids'))) {
            $decoded = json_decode($this->input('addon_ids'), true);
            if (is_array($decoded)) {
                $this->merge(['addon_ids' => $decoded]);
            }
        }
    }
}
