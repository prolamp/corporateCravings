<?php

namespace App\Modules\Orders\Http\Requests;

use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user()?->can('updateStatus', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('order'))],
            'note' => ['nullable', 'string', 'max:1000'],
            'delivery_person_name' => ['nullable', 'string', 'max:160'],
        ];
    }
}
