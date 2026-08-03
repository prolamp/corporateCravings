<?php

namespace Database\Factories;

use App\Modules\Addons\Models\Addon;
use App\Support\Enums\AddonType;
use Database\Factories\Concerns\HasUniqueNaming;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    use HasUniqueNaming;

    protected $model = Addon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->uniqueName();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'type' => fake()->randomElement(AddonType::values()),
            'price' => fake()->numberBetween(50, 400),
            'image' => null,
            'is_global' => true,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function forDishesOnly(): static
    {
        return $this->state(fn () => ['is_global' => false]);
    }
}
