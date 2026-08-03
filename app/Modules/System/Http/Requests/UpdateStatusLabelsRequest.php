<?php

namespace App\Modules\System\Http\Requests;

use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusLabelsRequest extends FormRequest
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
            'labels' => ['required', 'array'],
        ];

        foreach (StatusRegistry::groups() as $group) {
            $rules["labels.{$group}"] = ['sometimes', 'array'];
            foreach (StatusRegistry::codes($group) as $code) {
                $rules["labels.{$group}.{$code}"] = ['nullable', 'string', 'max:80'];
            }
        }

        return $rules;
    }
}
