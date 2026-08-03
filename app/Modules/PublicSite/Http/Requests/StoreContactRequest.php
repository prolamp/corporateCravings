<?php

namespace App\Modules\PublicSite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
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
        $subjects = [
            'General inquiry',
            'Corporate packages',
            'Daily lunch / schedule',
            'Bulk / event order',
            'Other',
        ];

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', Rule::in($subjects)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
