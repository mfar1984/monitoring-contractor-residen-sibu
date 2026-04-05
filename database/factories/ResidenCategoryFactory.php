<?php

namespace Database\Factories;

use App\Models\ResidenCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResidenCategory>
 */
class ResidenCategoryFactory extends Factory
{
    protected $model = ResidenCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Residen ' . fake()->city(),
            'code' => 'R' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'description' => fake()->sentence(),
            'status' => 'Active',
        ];
    }

    /**
     * Indicate that the residen category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
