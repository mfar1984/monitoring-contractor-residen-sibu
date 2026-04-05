<?php

namespace Database\Factories;

use App\Models\Parliament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parliament>
 */
class ParliamentFactory extends Factory
{
    protected $model = Parliament::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Parlimen ' . fake()->city(),
            'code' => 'P' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'description' => fake()->sentence(),
            'status' => 'Active',
        ];
    }

    /**
     * Indicate that the parliament is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
