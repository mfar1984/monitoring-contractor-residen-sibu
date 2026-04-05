<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContractorAnalysisTransfer;
use App\Models\Project;
use App\Models\ContractorCategory;
use App\Models\AgencyCategory;
use App\Models\ResidenCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractorAnalysisTransferControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Residen user can access contractor analysis list page
     */
    public function test_residen_user_can_access_contractor_analysis_list(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $response = $this->actingAs($user)->get('/pages/contractor-analysis');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.contractor-analysis');
    }

    /**
     * Test non-Residen user gets 403 error on list page
     */
    public function test_non_residen_user_gets_403_on_list_page(): void
    {
        $agencyCategory = AgencyCategory::factory()->create();
        $user = User::factory()->create([
            'agency_category_id' => $agencyCategory->id,
            'residen_category_id' => null,
        ]);
        
        $response = $this->actingAs($user)->get('/pages/contractor-analysis');
        
        $response->assertStatus(403);
    }

    /**
     * Test Residen user can access create page
     */
    public function test_residen_user_can_access_create_page(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $response = $this->actingAs($user)->get('/pages/contractor-analysis/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.contractor-analysis-create');
        $response->assertViewHas('projects');
        $response->assertViewHas('upkjClasses');
        $response->assertViewHas('upkjHeads');
        $response->assertViewHas('upkjSubheads');
    }

    /**
     * Test non-Residen user gets 403 error on create page
     */
    public function test_non_residen_user_gets_403_on_create_page(): void
    {
        $agencyCategory = AgencyCategory::factory()->create();
        $user = User::factory()->create([
            'agency_category_id' => $agencyCategory->id,
            'residen_category_id' => null,
        ]);
        
        $response = $this->actingAs($user)->get('/pages/contractor-analysis/create');
        
        $response->assertStatus(403);
    }

    /**
     * Test transfer creation with valid data
     */
    public function test_transfer_creation_with_valid_data(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create(['status' => 'Active']);
        $contractors = ContractorCategory::factory()->count(3)->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E', 'F'],
            'upkj_heads' => ['I', 'II'],
            'upkj_subheads' => ['1(a)', '2(b)'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect('/pages/contractor-analysis');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('contractor_analysis_transfers', [
            'created_by' => $user->id,
            'status' => 'Draft',
        ]);
        
        // Verify project status changed
        $this->assertEquals('Analysis Pending', $project->fresh()->status);
    }

    /**
     * Test transfer creation validation - project required
     */
    public function test_transfer_creation_requires_project(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $contractors = ContractorCategory::factory()->count(2)->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'upkj_classes' => ['E'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $response->assertSessionHasErrors('project_id');
    }

    /**
     * Test transfer creation validation - at least one UPKJ filter required
     */
    public function test_transfer_creation_requires_upkj_filter(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create();
        $contractors = ContractorCategory::factory()->count(2)->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $response->assertSessionHasErrors();
    }

    /**
     * Test transfer creation validation - contractors required
     */
    public function test_transfer_creation_requires_contractors(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create();
        
        $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'contractor_ids' => [],
        ]);
        
        $response->assertSessionHasErrors('contractor_ids');
    }

    /**
     * Test Residen user can view transfer details
     */
    public function test_residen_user_can_view_transfer_details(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'created_by' => $user->id,
        ]);
        
        $response = $this->actingAs($user)->get("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.contractor-analysis-show');
        $response->assertViewHas('transfer');
    }

    /**
     * Test non-Residen user gets 403 error on show page
     */
    public function test_non_residen_user_gets_403_on_show_page(): void
    {
        $agencyCategory = AgencyCategory::factory()->create();
        $user = User::factory()->create([
            'agency_category_id' => $agencyCategory->id,
            'residen_category_id' => null,
        ]);
        
        $transfer = ContractorAnalysisTransfer::factory()->create();
        
        $response = $this->actingAs($user)->get("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertStatus(403);
    }

    /**
     * Test Draft transfer can be deleted
     */
    public function test_draft_transfer_can_be_deleted(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create(['status' => 'Analysis Pending']);
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'status' => 'Draft',
            'created_by' => $user->id,
        ]);
        
        $transfer->projects()->attach($project->id);
        
        $response = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertRedirect('/pages/contractor-analysis');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('contractor_analysis_transfers', [
            'id' => $transfer->id,
        ]);
        
        // Verify project status rollback
        $this->assertEquals('Active', $project->fresh()->status);
    }

    /**
     * Test non-Draft transfer cannot be deleted
     */
    public function test_non_draft_transfer_cannot_be_deleted(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'status' => 'Submitted',
            'created_by' => $user->id,
        ]);
        
        $response = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('contractor_analysis_transfers', [
            'id' => $transfer->id,
        ]);
    }

    /**
     * Test non-Residen user gets 403 error on delete
     */
    public function test_non_residen_user_gets_403_on_delete(): void
    {
        $agencyCategory = AgencyCategory::factory()->create();
        $user = User::factory()->create([
            'agency_category_id' => $agencyCategory->id,
            'residen_category_id' => null,
        ]);
        
        $transfer = ContractorAnalysisTransfer::factory()->create(['status' => 'Draft']);
        
        $response = $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        
        $response->assertStatus(403);
    }

    /**
     * Test project status changes to Analysis Pending on transfer creation
     */
    public function test_project_status_changes_on_transfer_creation(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create(['status' => 'Active']);
        $contractors = ContractorCategory::factory()->count(2)->create();
        
        $this->assertEquals('Active', $project->status);
        
        $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $this->assertEquals('Analysis Pending', $project->fresh()->status);
    }

    /**
     * Test project status rollback on transfer deletion
     */
    public function test_project_status_rollback_on_transfer_deletion(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create(['status' => 'Analysis Pending']);
        $transfer = ContractorAnalysisTransfer::factory()->create([
            'status' => 'Draft',
            'created_by' => $user->id,
        ]);
        
        $transfer->projects()->attach($project->id);
        
        $this->assertEquals('Analysis Pending', $project->status);
        
        $this->actingAs($user)->delete("/pages/contractor-analysis/{$transfer->id}");
        
        $this->assertEquals('Active', $project->fresh()->status);
    }

    /**
     * Test transfer stores UPKJ filters correctly
     */
    public function test_transfer_stores_upkj_filters_correctly(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create();
        $contractors = ContractorCategory::factory()->count(2)->create();
        
        $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E', 'F', 'EX'],
            'upkj_heads' => ['I', 'II'],
            'upkj_subheads' => ['1(a)', '2(b)', '3(c)'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $transfer = ContractorAnalysisTransfer::latest()->first();
        
        $this->assertEquals(['E', 'F', 'EX'], $transfer->upkj_classes);
        $this->assertEquals(['I', 'II'], $transfer->upkj_heads);
        $this->assertEquals(['1(a)', '2(b)', '3(c)'], $transfer->upkj_subheads);
    }

    /**
     * Test transfer attaches contractors correctly
     */
    public function test_transfer_attaches_contractors_correctly(): void
    {
        $residenCategory = ResidenCategory::factory()->create();
        $user = User::factory()->create([
            'residen_category_id' => $residenCategory->id,
        ]);
        
        $project = Project::factory()->create();
        $contractors = ContractorCategory::factory()->count(5)->create();
        
        $this->actingAs($user)->post('/pages/contractor-analysis', [
            'project_id' => $project->id,
            'upkj_classes' => ['E'],
            'contractor_ids' => $contractors->pluck('id')->toArray(),
        ]);
        
        $transfer = ContractorAnalysisTransfer::latest()->first();
        
        $this->assertCount(5, $transfer->contractors);
        $this->assertEquals($contractors->pluck('id')->sort()->values(), $transfer->contractors->pluck('id')->sort()->values());
    }
}
