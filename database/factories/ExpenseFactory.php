<?php

namespace Database\Factories;

use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->numberBetween(100, 4000);

        return [
            'expense_category_id' => ExpenseCategory::factory(),
            'title' => fake()->word().' '.fake()->word(),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['kg', 'litre', 'piece', null]),
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
            'date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'status' => 'pending',
            'created_by' => null,
            'receipt_image' => null,
        ];
    }

    public function grocery(string $title = 'Chicken', string $unit = 'kg'): static
    {
        return $this->state(function () use ($title, $unit) {
            $category = ExpenseCategory::query()->firstOrCreate(
                ['name' => 'Groceries'],
                ['description' => 'Groceries', 'is_active' => true],
            );

            return [
                'expense_category_id' => $category->id,
                'title' => $title,
                'unit' => $unit,
                'status' => 'approved',
            ];
        });
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}
