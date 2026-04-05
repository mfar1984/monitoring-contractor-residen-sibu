<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContractorCategory;
use App\Models\ContractorUpkjRecord;
use App\Models\ResidenCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractorFilterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test contractor filtering with classes only
     */
    public function test_filter_contractors_by_classes_only(): void
    {
        $user = User::factory()->create();
        
        // Create contractors with UPKJ records
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        $contractor3 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor3->id,
            'classifications' => [['class' => 'G', 'head' => 'III', 'subhead' => '3(c)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E', 'F'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    /**
     * Test contractor filtering with heads only
     */
    public function test_filter_contractors_by_heads_only(): void
    {
        $user = User::factory()->create();
        
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'heads' => ['I'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /**
     * Test contractor filtering with subheads only
     */
    public function test_filter_contractors_by_subheads_only(): void
    {
        $user = User::factory()->create();
        
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'subheads' => ['1(a)'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /**
     * Test contractor filtering with multiple filters
     */
    public function test_filter_contractors_with_multiple_filters(): void
    {
        $user = User::factory()->create();
        
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        $contractor3 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor3->id,
            'classifications' => [['class' => 'G', 'head' => 'III', 'subhead' => '3(c)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
            'heads' => ['I'],
            'subheads' => ['1(a)'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /**
     * Test error when no filter provided
     */
    public function test_error_when_no_filter_provided(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', []);
        
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'At least one UPKJ filter must be provided',
        ]);
    }

    /**
     * Test response format and structure
     */
    public function test_response_format_and_structure(): void
    {
        $user = User::factory()->create();
        
        $contractor = ContractorCategory::factory()->create([
            'company_name' => 'Test Company Sdn Bhd',
            'registration_number' => '1234567890',
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [
                ['class' => 'E', 'head' => 'I', 'subhead' => '1(a)'],
                ['class' => 'F', 'head' => 'II', 'subhead' => '2(b)'],
            ],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'company_name',
                'registration_number',
                'upkj_records' => [
                    '*' => [
                        'id',
                        'classifications',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test filtering matches ANY selected criteria (OR logic)
     */
    public function test_filtering_uses_or_logic(): void
    {
        $user = User::factory()->create();
        
        $contractor1 = ContractorCategory::factory()->create();
        $contractor2 = ContractorCategory::factory()->create();
        $contractor3 = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor1->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor2->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor3->id,
            'classifications' => [['class' => 'G', 'head' => 'III', 'subhead' => '3(c)']],
        ]);
        
        // Should match contractors with class E OR F (2 contractors)
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E', 'F'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    /**
     * Test no contractors found returns empty array
     */
    public function test_no_contractors_found_returns_empty_array(): void
    {
        $user = User::factory()->create();
        
        $contractor = ContractorCategory::factory()->create();
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['Z'], // Non-existent class
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    /**
     * Test authentication required for API endpoint
     */
    public function test_authentication_required_for_api_endpoint(): void
    {
        $response = $this->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
        ]);
        
        $response->assertStatus(401);
    }

    /**
     * Test contractor with multiple UPKJ records
     */
    public function test_contractor_with_multiple_upkj_records(): void
    {
        $user = User::factory()->create();
        
        $contractor = ContractorCategory::factory()->create();
        
        // Create multiple UPKJ records for same contractor
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [['class' => 'E', 'head' => 'I', 'subhead' => '1(a)']],
        ]);
        
        ContractorUpkjRecord::factory()->create([
            'contractor_category_id' => $contractor->id,
            'classifications' => [['class' => 'F', 'head' => 'II', 'subhead' => '2(b)']],
        ]);
        
        $response = $this->actingAs($user)->postJson('/api/contractors/filter-by-upkj', [
            'classes' => ['E'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonCount(1); // Should return contractor once, not duplicate
        
        $data = $response->json();
        $this->assertCount(2, $data[0]['upkj_records']); // But should include all UPKJ records
    }
}
