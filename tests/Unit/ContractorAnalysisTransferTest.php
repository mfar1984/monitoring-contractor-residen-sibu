<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ContractorAnalysisTransfer;
use App\Models\User;
use App\Models\Project;
use App\Models\ContractorCategory;
use App\Models\AgencyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractorAnalysisTransferTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test transfer number generation is unique
     */
    public function test_generate_transfer_number_is_unique(): void
    {
        $number1 = ContractorAnalysisTransfer::generateTransferNumber();
        $number2 = ContractorAnalysisTransfer::generateTransferNumber();
        
        $this->assertNotEquals($number1, $number2);
        $this->assertStringStartsWith('CA/' . date('Y') . '/', $number1);
        $this->assertStringStartsWith('CA/' . date('Y') . '/', $number2);
    }

    /**
     * Test transfer number format is correct
     */
    public function test_transfer_number_format(): void
    {
        $number = ContractorAnalysisTransfer::generateTransferNumber();
        
        $this->assertMatchesRegularExpression('/^CA\/\d{4}\/\d{3}$/', $number);
    }

    /**
     * Test transfer number increments correctly
     */
    public function test_transfer_number_increments(): void
    {
        // Create first transfer
        ContractorAnalysisTransfer::factory()->create([
            'transfer_number' => 'CA/' . date('Y') . '/001'
        ]);
        
        // Generate next number
        $nextNumber = ContractorAnalysisTransfer::generateTransferNumber();
        
        $this->assertEquals('CA/' . date('Y') . '/002', $nextNumber);
    }

    /**
     * Test UPKJ filter JSON storage and retrieval
     */
    public function test_upkj_filter_json_storage(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => ['E', 'F', 'EX'],
            'upkj_heads' => ['I', 'II', 'III'],
            'upkj_subheads' => ['1(a)', '1(b)', '2(a)'],
        ]);
        
        $this->assertIsArray($transfer->upkj_classes);
        $this->assertIsArray($transfer->upkj_heads);
        $this->assertIsArray($transfer->upkj_subheads);
        
        $this->assertContains('E', $transfer->upkj_classes);
        $this->assertContains('I', $transfer->upkj_heads);
        $this->assertContains('1(a)', $transfer->upkj_subheads);
    }

    /**
     * Test getFormattedUpkjFilter method with all filters
     */
    public function test_get_formatted_upkj_filter_with_all_filters(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => ['E', 'F'],
            'upkj_heads' => ['I', 'II'],
            'upkj_subheads' => ['1(a)', '2(b)'],
        ]);
        
        $formatted = $transfer->getFormattedUpkjFilter();
        
        $this->assertStringContainsString('Class: E, F', $formatted);
        $this->assertStringContainsString('Head: I, II', $formatted);
        $this->assertStringContainsString('Subhead: 1(a), 2(b)', $formatted);
    }

    /**
     * Test getFormattedUpkjFilter method with partial filters
     */
    public function test_get_formatted_upkj_filter_with_partial_filters(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => ['E'],
            'upkj_heads' => null,
            'upkj_subheads' => null,
        ]);
        
        $formatted = $transfer->getFormattedUpkjFilter();
        
        $this->assertStringContainsString('Class: E', $formatted);
        $this->assertStringNotContainsString('Head:', $formatted);
        $this->assertStringNotContainsString('Subhead:', $formatted);
    }

    /**
     * Test getFormattedUpkjFilter method with no filters
     */
    public function test_get_formatted_upkj_filter_with_no_filters(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => null,
            'upkj_heads' => null,
            'upkj_subheads' => null,
        ]);
        
        $formatted = $transfer->getFormattedUpkjFilter();
        
        $this->assertEquals('No filter applied', $formatted);
    }

    /**
     * Test hasUpkjFilter method returns true when filters exist
     */
    public function test_has_upkj_filter_returns_true(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => ['E'],
            'upkj_heads' => null,
            'upkj_subheads' => null,
        ]);
        
        $this->assertTrue($transfer->hasUpkjFilter());
    }

    /**
     * Test hasUpkjFilter method returns false when no filters
     */
    public function test_has_upkj_filter_returns_false(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => null,
            'upkj_heads' => null,
            'upkj_subheads' => null,
        ]);
        
        $this->assertFalse($transfer->hasUpkjFilter());
    }

    /**
     * Test creator relationship
     */
    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'created_by' => $user->id,
        ]);
        
        $this->assertInstanceOf(User::class, $transfer->creator);
        $this->assertEquals($user->id, $transfer->creator->id);
    }

    /**
     * Test agency relationship
     */
    public function test_agency_relationship(): void
    {
        $agency = AgencyCategory::factory()->create();
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'agency_category_id' => $agency->id,
        ]);
        
        $this->assertInstanceOf(AgencyCategory::class, $transfer->agency);
        $this->assertEquals($agency->id, $transfer->agency->id);
    }

    /**
     * Test projects relationship
     */
    public function test_projects_relationship(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create();
        $project = Project::factory()->create();
        
        $transfer->projects()->attach($project->id);
        
        $this->assertCount(1, $transfer->projects);
        $this->assertInstanceOf(Project::class, $transfer->projects->first());
    }

    /**
     * Test contractors relationship
     */
    public function test_contractors_relationship(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create();
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        
        $transfer->contractors()->attach([$contractor1->id, $contractor2->id]);
        
        $this->assertCount(2, $transfer->contractors);
        $this->assertInstanceOf(ContractorCategory::class, $transfer->contractors->first());
    }

    /**
     * Test single project constraint
     */
    public function test_single_project_per_transfer(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        
        // Attach first project
        $transfer->projects()->attach($project1->id);
        $this->assertCount(1, $transfer->fresh()->projects);
        
        // Detach and attach second project (simulating single project constraint)
        $transfer->projects()->detach();
        $transfer->projects()->attach($project2->id);
        
        $this->assertCount(1, $transfer->fresh()->projects);
        $this->assertEquals($project2->id, $transfer->fresh()->projects->first()->id);
    }

    /**
     * Test multiple contractors can be attached
     */
    public function test_multiple_contractors_can_be_attached(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create();
        $contractors = ContractorCategory::factory()->count(5)->create();
        
        $transfer->contractors()->attach($contractors->pluck('id')->toArray());
        
        $this->assertCount(5, $transfer->fresh()->contractors);
    }

    /**
     * Test transfer can be created without UPKJ filters
     */
    public function test_transfer_can_be_created_without_upkj_filters(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'upkj_classes' => null,
            'upkj_heads' => null,
            'upkj_subheads' => null,
        ]);
        
        $this->assertNull($transfer->upkj_classes);
        $this->assertNull($transfer->upkj_heads);
        $this->assertNull($transfer->upkj_subheads);
        $this->assertFalse($transfer->hasUpkjFilter());
    }

    /**
     * Test transfer status defaults to Draft
     */
    public function test_transfer_status_defaults_to_draft(): void
    {
        $transfer = ContractorAnalysisTransfer::factory()->create();
        
        $this->assertEquals('Draft', $transfer->status);
    }

    /**
     * Test transfer can have different statuses
     */
    public function test_transfer_can_have_different_statuses(): void
    {
        $draft = ContractorAnalysisTransfer::factory()->create(['status' => 'Draft']);
        $submitted = ContractorAnalysisTransfer::factory()->create(['status' => 'Submitted']);
        $inAnalysis = ContractorAnalysisTransfer::factory()->create(['status' => 'In Analysis']);
        $completed = ContractorAnalysisTransfer::factory()->create(['status' => 'Completed']);
        
        $this->assertEquals('Draft', $draft->status);
        $this->assertEquals('Submitted', $submitted->status);
        $this->assertEquals('In Analysis', $inAnalysis->status);
        $this->assertEquals('Completed', $completed->status);
    }
}
