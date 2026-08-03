<?php

namespace App\Modules\Subscribers\Http\Requests;

use App\Models\User;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $subscriber */
        $subscriber = $this->route('subscriber');

        return [
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['required', 'string', 'max:160'],
            'phone' => [
                'required',
                'string',
                'max:32',
                Rule::unique('users', 'phone')->ignore($subscriber->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($subscriber->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('user'))],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
