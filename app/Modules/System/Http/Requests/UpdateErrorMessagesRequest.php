<?php

namespace App\Modules\System\Http\Requests;

use App\Support\ErrorCatalog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateErrorMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('statuses.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'messages' => ['required', 'array'],
        ];

        foreach (ErrorCatalog::codes() as $code) {
            $rules["messages.{$code}"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
