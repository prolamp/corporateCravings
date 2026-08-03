<?php

namespace App\Modules\Subscribers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillSlipRequest extends FormRequest
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
            'schedule_ids' => ['sometimes', 'array', 'min:1'],
            'schedule_ids.*' => ['integer', 'exists:subscriber_schedules,id'],
            'adjustment' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
