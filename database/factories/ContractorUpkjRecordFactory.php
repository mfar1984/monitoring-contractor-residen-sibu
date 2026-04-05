<?php

namespace Database\Factories;

use App\Models\ContractorUpkjRecord;
use App\Models\ContractorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractorUpkjRecord>
 */
class ContractorUpkjRecordFactory extends Factory
{
    protected $model = ContractorUpkjRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classes = ['E', 'F', 'G', 'EX'];
        $heads = ['I', 'II', 'III', 'IV', 'V'];
        $subheads = ['1(a)', '1(b)', '2(a)', '2(b)', '3(a)', '3(b)'];
        
        return [
            'contractor_category_id' => ContractorCategory::factory(),
            'classifications' => [
                [
                    'class' => fake()->randomElement($classes),
                    'head' => fake()->randomElement($heads),
                    'subhead' => fake()->randomElement($subheads),
                ],
            ],
        ];
    }

    /**
     * Indicate that the record has multiple classifications.
     */
    public function withMultipleClassifications(): static
    {
        $classes = ['E', 'F', 'G', 'EX'];
        $heads = ['I', 'II', 'III', 'IV', 'V'];
        $subheads = ['1(a)', '1(b)', '2(a)', '2(b)', '3(a)', '3(b)'];
        
        return $this->state(fn (array $attributes) => [
            'classifications' => [
                [
                    'class' => fake()->randomElement($classes),
                    'head' => fake()->randomElement($heads),
                    'subhead' => fake()->randomElement($subheads),
                ],
                [
                    'class' => fake()->randomElement($classes),
                    'head' => fake()->randomElement($heads),
                    'subhead' => fake()->randomElement($subheads),
                ],
                [
                    'class' => fake()->randomElement($classes),
                    'head' => fake()->randomElement($heads),
                    'subhead' => fake()->randomElement($subheads),
                ],
            ],
        ]);
    }
}
