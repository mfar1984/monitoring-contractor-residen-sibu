<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContractorAnalysisTransfer;
use App\Models\Project;
use App\Models\ContractorCategory;
use App\Models\ContractorUpkjRecord;
use App\Models\ResidenCategory;
use App\Models\AgencyCategory;
use App\Models\Parliament;
use App\Models\Dun;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractorAnalysisTransferIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete transfer creation workflow
     */
    public function test_complete_transfer_creation_workflow(): void
    {
        // Setup: Create Residen user
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        // Setup: Create active project
        $project = Project::factory()->create(['status' => 'Active']);
        
        // Setup: Create contractors with UPKJ records
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        $contractor3 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'E', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor3->id,
            'classifications' => [['class' => 'F', 'head' => 'III', 'subhead' => '3(c)']],
        ]);
        
        // Step 1: Access create page
        $response = $this->actingAs($user)->get('/pages/contractor-analysis/create');
        $response->assertStatus(200);
        
        // Step 2: Filter contractors by UPKJ (API call)
        $filterResponse = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
        ]);
        $filterResponse->assertStatus(200);
        $filterResponse->assertJsonCount(2); // Should return contractor1 and contractor2
        
        // Step 3: Create transfer with selected contractors
        $createResponse = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'upkj_heads' => ['I', 'II'],
            'upkj_subheads' => ['1(a)', '2(b)'],
            'contractor_ids' => [$contractor1->id, $contractor2->id],
        ]);
        
        $createResponse->assertRedirect('/pages/contractor-analysis');
        $createResponse->assertSessionHas('success');
        
        // Step 4: Verify transfer created
        $transfer = ContractorAnalysisTransfer::latest()->first();
        $this->assertNotNull($transfer);
        $this->assertEquals('Draft', $transfer->status);
        $this->assertEquals($user->id, $transfer->created_by);
        
        // Step 5: Verify project attached
        $this->assertCount(1, $transfer->projects);
        $this->assertEquals($project->id, $transfer->projects->first()->id);
        
        // Step 6: Verify contractors attached
        $this->assertCount(2, $transfer->contractors);
        $this->assertTrue($transfer->contractors->contains($contractor1));
        $this->assertTrue($transfer->contractors->contains($contractor2));
        
        // Step 7: Verify project status changed
        $this->assertEquals('Analysis Pending', $project->fresh()->status);
        
        // Step 8: View transfer details
        $showResponse = $this->actingAs($user)->get("/pages/contractor-analysis/{$transfer->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertViewHas('transfer');
        
        // Step 9: Delete transfer
        $deleteResponse = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        $deleteResponse->assertRedirect('/pages/contractor-analysis');
        $deleteResponse->assertSessionHas('success');
        
        // Step 10: Verify transfer deleted
        $this->assertDatabaseMissing('contractor_analysis_transfers', [
            'id' => $transfer->id,
        ]);
        
        // Step 11: Verify project status rollback
        $this->assertEquals('Active', $project->fresh()->status);
    }

    /**
     * Test workflow with different user roles
     */
    public function test_workflow_with_different_user_roles(): void
    {
        // Create users with different roles
        $residenCategory = ResidenCategory::factory()->create();
        $agencyCategory = AgencyCategory::factory()->create();
        $parliament = Parliament::factory()->create();
        $dun = Dun::factory()->create();
        
        $residenUser = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        $agencyUser = User::factory()->create(['agency_category_id' => $agencyCategory->id]);
        $parliamentUser = User::factory()->create(['parliament_id' => $parliament->id]);
        $dunUser = User::factory()->create(['dun_id' => $dun->id]);
        
        // Residen user should have access
        $response = $this->actingAs($residenUser)->get('/pages/contractor-analysis');
        $response->assertStatus(200);
        
        // Agency user should get 403
        $response = $this->actingAs($agencyUser)->get('/pages/contractor-analysis');
        $response->assertStatus(403);
        
        // Parliament user should get 403
        $response = $this->actingAs($parliamentUser)->get('/pages/contractor-analysis');
        $response->assertStatus(403);
        
        // DUN user should get 403
        $response = $this->actingAs($dunUser)->get('/pages/contractor-analysis');
        $response->assertStatus(403);
    }

    /**
     * Test UPKJ filter to contractor list to selection to submission
     */
    public function test_upkj_filter_to_submission_flow(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $project = Project::factory()->create(['status' => 'Active']);
        
        // Create contractors with various UPKJ classifications
        $contractors = [];
        $upkjData = [
            ['class' => 'E', 'head' => 'I', 'subhead' => '1(a)'],
            ['class' => 'E', 'head' => 'II', 'subhead' => '2(b)'],
            ['class' => 'F', 'head' => 'I', 'subhead' => '1(a)'],
            ['class' => 'F', 'head' => 'III', 'subhead' => '3(c)'],
            ['class' => 'G', 'head' => 'IV', 'subhead' => '4(d)'],
        ];
        
        foreach ($upkjData as $data) {
            $contractor = ContractorCategory::factory()->create();
            ContractorUpkjRecord::factory()->create([
                'contractor_category_id' => $contractor->id,
                'classifications' => [$data],
            ]);
            $contractors[] = $contractor;
        }
        
        // Filter by class E - should return 2 contractors
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        
        // Filter by head I - should return 2 contractors (E-I and F-I)
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'heads' => ['I'],
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        
        // Filter by multiple criteria - should return 4 contractors (E or F)
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E', 'F'],
        ]);
        $response->assertStatus(200);
        $response->assertJsonCount(4);
        
        // Submit transfer with selected contractors
        $selectedContractors = [$contractors[0]->id, $contractors[1]->id, $contractors[2]->id];
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E', 'F'],
            'upkj_heads' => ['I', 'II'],
            'contractor_ids' => $selectedContractors,
        ]);
        
        $response->assertRedirect('/pages/contractor-analysis');
        
        $transfer = ContractorAnalysisTransfer::latest()->first();
        $this->assertCount(3, $transfer->contractors);
    }

    /**
     * Test transfer deletion workflow
     */
    public function test_transfer_deletion_workflow(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $project = Project::factory()->create(['status' => 'Active']);
        $contractors = ContractorCategory::factory()->count(3)->create();
        
        // Create transfer
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $transfer = ContractorAnalysisTransfer::latest()->first();
        
        // Verify initial state
        $this->assertEquals('Draft', $transfer->status);
        $this->assertEquals('Analysis Pending', $project->fresh()->status);
        $this->assertCount(1, $transfer->projects);
        $this->assertCount(3, $transfer->contractors);
        
        // Delete transfer
        $response = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        $response->assertRedirect('/pages/contractor-analysis');
        
        // Verify deletion
        $this->assertDatabaseMissing('contractor_analysis_transfers', ['id' => $transfer->id]);
        $this->assertDatabaseMissing('contractor_analysis_project', ['contractor_analysis_transfer_id' => $transfer->id]);
        $this->assertDatabaseMissing('contractor_analysis_transfer_contractor', ['contractor_analysis_transfer_id' => $transfer->id]);
        
        // Verify project status rollback
        $this->assertEquals('Active', $project->fresh()->status);
    }

    /**
     * Test edge case: no available projects
     */
    public function test_edge_case_no_available_projects(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        // All projects are in analysis or cancelled
        Project::factory()->create(['status' => 'Analysis Pending']);
        Project::factory()->create(['status' => 'Cancelled']);
        
        $response = $this->actingAs($user)->get('/pages/contractor-analysis/create');
        $response->assertStatus(200);
        $response->assertViewHas('projects', function ($projects) {
            return $projects->isEmpty();
        });
    }

    /**
     * Test edge case: no contractors matching UPKJ filter
     */
    public function test_edge_case_no_contractors_matching_filter(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $contractor = ContractorCategory::factory()->create();
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        // Filter with non-matching criteria
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['Z'], // Non-existent class
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    /**
     * Test edge case: all UPKJ filters selected
     */
    public function test_edge_case_all_upkj_filters_selected(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $project = Project::factory()->create(['status' => 'Active']);
        $contractor = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E', 'F', 'G', 'EX'],
            'upkj_heads' => ['I', 'II', 'III', 'IV', 'V'],
            'upkj_subheads' => ['1(a)', '1(b)', '2(a)', '2(b)', '3(a)', '3(b)'],
            'contractor_ids' => [$contractor->id],
        ]);
        
        $response->assertRedirect('/pages/contractor-analysis');
        
        $transfer = ContractorAnalysisTransfer::latest()->first();
        $this->assertCount(4, $transfer->upkj_classes);
        $this->assertCount(5, $transfer->upkj_heads);
        $this->assertCount(6, $transfer->upkj_subheads);
    }

    /**
     * Test validation: form submission without project selection
     */
    public function test_validation_form_submission_without_project(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $contractor = ContractorCategory::factory()->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'upkj_classes' => ['E'],
            'contractor_ids' => [$contractor->id],
        ]);
        
        $response->assertSessionHasErrors('project_id');
    }

    /**
     * Test validation: form submission without UPKJ filter
     */
    public function test_validation_form_submission_without_upkj_filter(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $project = Project::factory()->create();
        $contractor = ContractorCategory::factory()->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'contractor_ids' => [$contractor->id],
        ]);
        
        $response->assertSessionHasErrors();
    }

    /**
     * Test validation: form submission without contractor selection
     */
    public function test_validation_form_submission_without_contractors(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $project = Project::factory()->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'contractor_ids' => [],
        ]);
        
        $response->assertSessionHasErrors('contractor_ids');
    }

    /**
     * Test deleting non-Draft transfer fails
     */
    public function test_deleting_non_draft_transfer_fails(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create(['residen_category_id' => $residenCategory->id]);
        
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'status' => 'Submitted',
            'created_by' => $user->id,
        ]);
        
        $response = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Transfer should still exist
        $this->assertDatabaseHas('contractor_analysis_transfers', ['id' => $transfer->id]);
    }
}
