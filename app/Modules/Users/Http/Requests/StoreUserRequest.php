<?php

namespace App\Modules\Users\Http\Requests;

use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.view')
            && count(RoleAccess::creatableRoles($this->user())) > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creatable = array_map(
            fn (UserRole $role) => $role->value,
            RoleAccess::creatableRoles($this->user()),
        );

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['nullable', 'string', Password::defaults()],
            'address' => ['nullable', 'string', 'max:500'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('user'))],
            'role' => ['required', 'string', Rule::in($creatable)],
            'image' => ['nullable', 'image', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'You are not allowed to assign that role.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
        ];
    }
}
