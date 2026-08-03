<?php

namespace Database\Factories;

use App\Modules\Delivery\Models\DeliveryZone;
use Database\Factories\Concerns\HasUniqueNaming;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
{
    use HasUniqueNaming;

    protected $model = DeliveryZone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->uniqueName(),
            'fee' => fake()->numberBetween(0, 500),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
