<?php

namespace App\Modules\PublicSite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceCheckoutOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s]{8,20}$/'],
            // Email identifies the account: existing emails reuse that user (no unique error).
            'customer_email' => ['required', 'email', 'max:160'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_zone_id' => [
                'required',
                'integer',
                Rule::exists('delivery_zones', 'id')->where(fn ($q) => $q->where('is_active', true)->whereNull('deleted_at')),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Full name is required.',
            'customer_phone.required' => 'Phone number is required.',
            'customer_phone.regex' => 'Enter a valid phone number.',
            'customer_email.required' => 'Email is required.',
            'customer_email.email' => 'Enter a valid email address.',
            'delivery_address.required' => 'Street address is required.',
            'delivery_zone_id.required' => 'Please select a delivery zone.',
            'delivery_zone_id.exists' => 'Please select a valid delivery zone.',
            'delivery_zone_id.integer' => 'Please select a delivery zone.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('customer_phone')) {
            $merge['customer_phone'] = preg_replace('/\s+/', '', (string) $this->input('customer_phone'));
        }

        if ($this->has('customer_email')) {
            $merge['customer_email'] = strtolower(trim((string) $this->input('customer_email')));
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
