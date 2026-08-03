<?php

namespace App\Modules\PublicSite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'key' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }
}
