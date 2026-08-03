<?php

namespace App\Modules\Subscribers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillSlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bill_slips.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subscribe_contract_id' => ['required', 'integer', 'exists:subscribe_contracts,id'],
            'schedule_ids' => ['required', 'array', 'min:1'],
            'schedule_ids.*' => ['integer', 'exists:subscriber_schedules,id'],
            'adjustment' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
