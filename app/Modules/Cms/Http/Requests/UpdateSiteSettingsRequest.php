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
            'values.receipt' => ['required', 'array'],
            'values.receipt.manager_name' => ['nullable', 'string', 'max:160'],
            'values.receipt.bank_label' => ['nullable', 'string', 'max:160'],
            'values.receipt.bank_account_number' => ['nullable', 'string', 'max:64'],
            'values.receipt.description' => ['nullable', 'string', 'max:2000'],
            'signature' => ['nullable', 'image', 'max:10240'],
            'remove_signature' => ['sometimes', 'boolean'],
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

    protected function prepareForValidation(): void
    {
        if ($this->has('remove_signature')) {
            $this->merge([
                'remove_signature' => filter_var(
                    $this->input('remove_signature'),
                    FILTER_VALIDATE_BOOLEAN,
                ),
            ]);
        }
    }
}
