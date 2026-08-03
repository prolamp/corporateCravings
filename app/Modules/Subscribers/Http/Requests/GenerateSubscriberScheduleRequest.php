<?php

namespace App\Modules\Subscribers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSubscriberScheduleRequest extends FormRequest
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
            'contract_id' => ['required', 'integer', 'exists:subscribe_contracts,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
