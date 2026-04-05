<?php

namespace Database\Factories;

use App\Models\ContractorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractorCategory>
 */
class ContractorCategoryFactory extends Factory
{
    protected $model = ContractorCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company() . ' Sdn Bhd',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'registration_number' => fake()->numerify('##########'),
            'description' => fake()->sentence(),
            'status' => 'Active',
        ];
    }

    /**
     * Indicate that the contractor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
