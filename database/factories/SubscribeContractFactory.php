<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Support\Enums\MealType;
use App\Support\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscribeContract>
 */
class SubscribeContractFactory extends Factory
{
    protected $model = SubscribeContract::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $packageAmount = fake()->numberBetween(800, 2500);
        $discount = fake()->numberBetween(0, 300);

        return [
            'user_id' => User::factory()->subscriber(),
            'package_id' => Package::factory(),
            'meal_type' => MealType::Lunch->value,
            'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'persons' => fake()->numberBetween(10, 80),
            'delivery_time' => '13:00',
            'package_amount' => $packageAmount,
            'discount' => $discount,
            'agreed_total' => $packageAmount - $discount,
            'status' => 'active',
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => null,
            'notes' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => 'expired',
            'ends_on' => now()->subDay()->toDateString(),
        ]);
    }

    /**
     * Contract owned by a specific subscriber user.
     */
    public function forUser(User $user): static
    {
        if (! $user->hasRole(UserRole::Subscriber->value)) {
            $user->assignRole(UserRole::Subscriber->value);
        }

        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
