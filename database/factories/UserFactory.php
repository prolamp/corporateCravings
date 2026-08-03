<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('03#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Assign a role after creation, creating it if the seeder has not run.
     */
    public function withRole(UserRole|string $role): static
    {
        $name = $role instanceof UserRole ? $role->value : $role;

        return $this->afterCreating(function (User $user) use ($name) {
            Role::findOrCreate($name, 'web');
            $user->assignRole($name);
        });
    }

    public function superAdmin(): static
    {
        return $this->withRole(UserRole::SuperAdmin);
    }

    public function admin(): static
    {
        return $this->withRole(UserRole::Admin);
    }

    public function manager(): static
    {
        return $this->withRole(UserRole::Manager);
    }

    public function assistant(): static
    {
        return $this->withRole(UserRole::Assistant);
    }

    public function subscriber(): static
    {
        return $this->withRole(UserRole::Subscriber);
    }

    public function customer(): static
    {
        return $this->withRole(UserRole::Customer);
    }

    /**
     * Corporate contact details used by subscriber contracts.
     */
    public function company(?string $name = null): static
    {
        return $this->state(fn () => [
            'company_name' => $name ?? fake()->company(),
            'address' => fake()->address(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
