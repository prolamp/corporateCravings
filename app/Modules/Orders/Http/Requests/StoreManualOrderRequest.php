<?php

namespace App\Modules\Orders\Http\Requests;

use App\Support\Enums\OrderType;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in([OrderType::Event->value, OrderType::Individual->value])],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('order'))],
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:160'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'delivery_person_name' => ['nullable', 'string', 'max:160'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_variation_id' => ['required', 'integer', 'exists:dish_variations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.addons' => ['nullable', 'array'],
            'items.*.addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'items.*.addons.*.quantity' => ['nullable', 'integer', 'min:1', 'max:10000'],
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
