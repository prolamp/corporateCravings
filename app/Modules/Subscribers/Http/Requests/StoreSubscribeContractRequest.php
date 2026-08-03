<?php

namespace App\Modules\Subscribers\Http\Requests;

use App\Support\Enums\MealType;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscribeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('contracts.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'meal_type' => ['required', 'string', Rule::in(MealType::values())],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['required', 'string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'persons' => ['required', 'integer', 'min:1', 'max:10000'],
            'delivery_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'package_amount' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('generic'))],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('days_of_week') && is_string($this->input('days_of_week'))) {
            $decoded = json_decode($this->input('days_of_week'), true);
            if (is_array($decoded)) {
                $this->merge(['days_of_week' => $decoded]);
            }
        }

        if ($this->input('delivery_time') === '') {
            $this->merge(['delivery_time' => null]);
        }
    }
}
