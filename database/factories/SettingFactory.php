<?php

namespace Database\Factories;

use App\Models\Setting;
use Database\Factories\Concerns\HasUniqueNaming;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    use HasUniqueNaming;

    protected $model = Setting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => Str::slug($this->uniqueName(), '_'),
            'value' => fake()->word(),
            'type' => 'string',
        ];
    }
}
