<?php

namespace Database\Factories;

use App\Modules\Packages\Models\Package;
use Database\Factories\Concerns\HasUniqueNaming;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    use HasUniqueNaming;

    protected $model = Package::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->uniqueName().' Package';
        $base = fake()->numberBetween(600, 2500);
        $discount = fake()->numberBetween(0, 200);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'image' => null,
            'base_amount_per_person' => $base,
            'discount_amount' => $discount,
            'discount_percent' => 0,
            'total_amount_per_person' => $base - $discount,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
