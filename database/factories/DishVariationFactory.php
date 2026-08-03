<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\Enums\VariationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DishVariation>
 */
class DishVariationFactory extends Factory
{
    protected $model = DishVariation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dish_id' => Dish::factory(),
            'label' => '1 Serving',
            'quantity' => 1,
            'unit' => VariationUnit::Serving->value,
            'price' => fake()->numberBetween(300, 1200),
            'type' => VariationType::Single,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    /**
     * Bulk kg tier sold through WhatsApp rather than the cart.
     */
    public function bulk(float $kg = 5): static
    {
        return $this->state(fn () => [
            'label' => (int) $kg.' KG',
            'quantity' => $kg,
            'unit' => VariationUnit::Kg->value,
            'price' => fake()->numberBetween(4000, 12000),
            'type' => VariationType::Bulk,
        ]);
    }
}
