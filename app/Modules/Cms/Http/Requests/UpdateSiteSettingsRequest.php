<?php

namespace App\Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'values' => ['required', 'array'],
            'values.brand' => ['required', 'array'],
            'values.brand.name' => ['nullable', 'string', 'max:160'],
            'values.brand.phone' => ['nullable', 'string', 'max:32'],
            'values.brand.whatsapp' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+]*$/'],
            'values.brand.email' => ['nullable', 'email', 'max:160'],
            'values.brand.address' => ['nullable', 'string', 'max:500'],
            'values.brand.hours' => ['nullable', 'string', 'max:160'],
            'values.analytics' => ['required', 'array'],
            'values.analytics.ga_measurement_id' => [
                'nullable',
                'string',
                'max:32',
                'regex:/^$|^G-[A-Z0-9]+$/i',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'values.brand.whatsapp.regex' => 'WhatsApp should be digits (optional +) with country code.',
            'values.analytics.ga_measurement_id.regex' => 'GA4 ID must look like G-XXXXXXXXXX or be empty.',
        ];
    }
}
