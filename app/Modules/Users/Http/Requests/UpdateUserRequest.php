<?php

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()?->can('update', $user) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $actor = $this->user();

        $creatable = array_map(
            fn (UserRole $role) => $role->value,
            RoleAccess::creatableRoles($actor),
        );

        // Allow keeping the current role even if the actor couldn't freshly create it
        // (e.g. editing own profile fields) — but only when not changing role.
        $currentRole = $user->primaryRoleName();
        $allowedRoles = $creatable;
        if ($currentRole && ! in_array($currentRole, $allowedRoles, true)) {
            if ($actor?->is($user) || $actor?->can('update', $user)) {
                $allowedRoles[] = $currentRole;
            }
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::defaults()],
            'address' => ['nullable', 'string', 'max:500'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('user'))],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
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
