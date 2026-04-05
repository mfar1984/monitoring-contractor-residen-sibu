<?php

namespace Database\Factories;

use App\Models\ContractorAnalysisTransfer;
use App\Models\User;
use App\Models\AgencyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractorAnalysisTransfer>
 */
class ContractorAnalysisTransferFactory extends Factory
{
    protected $model = ContractorAnalysisTransfer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transfer_number' => 'CA/' . date('Y') . '/' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'created_by' => User::factory(),
            'agency_category_id' => AgencyCategory::factory(),
            'status' => 'Draft',
            'upkj_classes' => null,
            'upkj_heads' => null,
            'upkj_subheads' => null,
            'attachment_path' => null,
        ];
    }

    /**
     * Indicate that the transfer has UPKJ filters applied.
     */
    public function withUpkjFilters(): static
    {
        return $this->state(fn (array $attributes) => [
            'upkj_classes' => ['E', 'F'],
            'upkj_heads' => ['I', 'II'],
            'upkj_subheads' => ['1(a)', '2(b)'],
        ]);
    }

    /**
     * Indicate that the transfer is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Submitted',
        ]);
    }

    /**
     * Indicate that the transfer is in analysis.
     */
    public function inAnalysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'In Analysis',
        ]);
    }

    /**
     * Indicate that the transfer is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Completed',
        ]);
    }
}
