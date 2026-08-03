<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Category;
use Database\Factories\Concerns\HasUniqueNaming;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    use HasUniqueNaming;

    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->uniqueName();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'image' => null,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
