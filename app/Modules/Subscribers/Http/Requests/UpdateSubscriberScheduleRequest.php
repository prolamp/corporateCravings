<?php

namespace App\Modules\Subscribers\Http\Requests;

use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriberScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('schedules.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dish_id' => ['nullable', 'integer', 'exists:dishes,id'],
            'persons' => ['required', 'integer', 'min:1', 'max:10000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('schedule'))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
