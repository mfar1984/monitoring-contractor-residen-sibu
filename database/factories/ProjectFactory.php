<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\AgencyCategory;
use App\Models\Parliament;
use App\Models\Dun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_number' => 'PROJ/' . date('Y') . '/' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'project_year' => date('Y'),
            'name' => fake()->sentence(6),
            'project_scope' => fake()->paragraph(),
            'agency_category_id' => AgencyCategory::factory(),
            'parliament_id' => Parliament::factory(),
            'dun_basic_id' => Dun::factory(),
            'status' => 'Active',
            'actual_project_cost' => fake()->randomFloat(2, 100000, 5000000),
            'total_cost' => fake()->randomFloat(2, 100000, 5000000),
        ];
    }

    /**
     * Indicate that the project is in analysis.
     */
    public function inAnalysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Analysis Pending',
        ]);
    }

    /**
     * Indicate that the project is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Cancelled',
        ]);
    }
}
