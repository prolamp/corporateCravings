<?php

namespace Database\Factories;

use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriberSchedule>
 */
class SubscriberScheduleFactory extends Factory
{
    protected $model = SubscriberSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $persons = fake()->numberBetween(10, 60);
        $unitPrice = fake()->numberBetween(600, 2000);

        return [
            'subscribe_contract_id' => SubscribeContract::factory(),
            'package_id' => null,
            'dish_id' => null,
            'date' => fake()->unique()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'week_of_month' => fake()->numberBetween(1, 4),
            'dish_title' => fake()->words(3, true),
            'package_title' => fake()->words(2, true),
            'persons' => $persons,
            'unit_price' => $unitPrice,
            'total' => $persons * $unitPrice,
            'status' => 'scheduled',
            'payment_status' => 'unpaid',
            'notes' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['status' => 'delivered']);
    }
}
