<?php

namespace Database\Factories;

use App\Models\Dun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dun>
 */
class DunFactory extends Factory
{
    protected $model = Dun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'DUN ' . fake()->city(),
            'code' => 'N' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'description' => fake()->sentence(),
            'status' => 'Active',
        ];
    }

    /**
     * Indicate that the DUN is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
