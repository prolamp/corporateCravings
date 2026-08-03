<?php

namespace App\Modules\Delivery\Http\Requests;

use App\Modules\Delivery\Models\DeliveryZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delivery_zones.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var DeliveryZone $zone */
        $zone = $this->route('delivery_zone');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('delivery_zones', 'name')->ignore($zone->id),
            ],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
