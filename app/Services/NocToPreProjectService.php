<?php

namespace App\Services;

use App\Models\Noc;
use App\Models\Project;
use App\Models\PreProject;
use Illuminate\Support\Facades\Log;

class NocToPreProjectService
{
    /**
     * Process NOC submission and create pre-projects for changed imported projects and new projects
     * 
     * @param Noc $noc
     * @return array Array of created PreProject records
     */
    public function processNocSubmission(Noc $noc): array
    {
        $createdPreProjects = [];
        
        // Get ALL project entries (including new projects without project_id)
        $allProjectEntries = $noc->getAllProjectEntries();
        
        foreach ($allProjectEntries as $entry) {
            $pivotData = (array) $entry->pivot;
            
            // Check if this is an imported project or a new project
            $isImportedProject = !empty($pivotData['project_id']);
            
            if ($isImportedProject) {
                // Process imported project with changes
                $project = Project::find($pivotData['project_id']);
                
                if (!$project) {
                    Log::warning("Project not found for NOC entry", [
                        'noc_id' => $noc->id,
                        'project_id' => $pivotData['project_id']
                    ]);
                    continue;
                }
                
                // Check if project has changes
                if (!$this->hasChanges($pivotData)) {
                    Log::info("Skipping imported project without changes", [
                        'noc_id' => $noc->id,
                        'project_id' => $project->id,
                        'project_number' => $pivotData['no_projek']
                    ]);
                    continue; // No changes, skip
                }
                
                try {
                    // Create new pre-project record from changes
                    $preProject = $this->createPreProjectFromNocChanges($project, $pivotData);
                    $createdPreProjects[] = $preProject;
                    
                    Log::info("Created pre-project from NOC changes", [
                        'noc_id' => $noc->id,
                        'project_id' => $project->id,
                        'pre_project_id' => $preProject->id,
                        'project_number' => $pivotData['no_projek']
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create pre-project from NOC changes", [
                        'noc_id' => $noc->id,
                        'project_id' => $project->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                // Process new project (Add New)
                try {
                    $preProject = $this->createPreProjectFromNewNocEntry($noc, $pivotData);
                    $createdPreProjects[] = $preProject;
                    
                    Log::info("Created pre-project from new NOC entry", [
                        'noc_id' => $noc->id,
                        'pre_project_id' => $preProject->id,
                        'project_name' => $pivotData['nama_projek_baru']
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create pre-project from new NOC entry", [
                        'noc_id' => $noc->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return $createdPreProjects;
    }
    
    /**
     * Check if a NOC project has changes that require pre-project creation
     * 
     * @param array $nocProjectData Pivot data from noc_project
     * @return bool
     */
    public function hasChanges(array $nocProjectData): bool
    {
        // Check if any of the "new" fields have values AND are different from original
        $hasNameChange = !empty($nocProjectData['nama_projek_baru']) && 
                        $nocProjectData['nama_projek_baru'] !== $nocProjectData['nama_projek_asal'];
        
        $hasCostChange = !empty($nocProjectData['kos_baru']) && 
                        $nocProjectData['kos_baru'] != $nocProjectData['kos_asal'];
        
        $hasAgencyChange = !empty($nocProjectData['agensi_pelaksana_baru']) && 
                          $nocProjectData['agensi_pelaksana_baru'] !== $nocProjectData['agensi_pelaksana_asal'];
        
        return $hasNameChange || $hasCostChange || $hasAgencyChange;
    }
    
    /**
     * Create pre-project record from NOC project data
     * 
     * @param Project $originalProject
     * @param array $nocProjectData Pivot data with changes
     * @return PreProject
     */
    public function createPreProjectFromNocChanges(Project $originalProject, array $nocProjectData): PreProject
    {
        // Copy ALL data from original project
        $preProjectData = [
            // Basic Information
            'name' => $originalProject->name,
            'residen_category_id' => $originalProject->residen_category_id,
            'agency_category_id' => $originalProject->agency_category_id,
            'parliament_id' => $originalProject->parliament_id,
            'dun_basic_id' => $originalProject->dun_basic_id,
            'project_category_id' => $originalProject->project_category_id,
            'project_scope' => $originalProject->project_scope,
            
            // Cost of Project - CRITICAL: Map to actual_project_cost, not total_cost
            'actual_project_cost' => $originalProject->total_cost, // From cancelled project
            'original_project_cost' => $originalProject->total_cost, // Store original for validation
            'consultation_cost' => 0, // Reset to 0 for user to fill
            'lss_inspection_cost' => 0, // Reset to 0 for user to fill
            'sst' => 0, // Reset to 0 for user to fill
            'others_cost' => 0, // Reset to 0 for user to fill
            'total_cost' => $originalProject->total_cost, // Initially same as actual
            
            // Project Location
            'implementation_period' => $originalProject->implementation_period,
            'division_id' => $originalProject->division_id,
            'district_id' => $originalProject->district_id,
            'parliament_location_id' => $originalProject->parliament_location_id,
            'dun_id' => $originalProject->dun_id,
            
            // Site Information
            'site_layout' => $originalProject->site_layout,
            'land_title_status_id' => $originalProject->land_title_status_id,
            'consultation_service' => $originalProject->consultation_service,
            
            // Implementation Details
            'implementing_agency_id' => $originalProject->implementing_agency_id,
            'implementation_method_id' => $originalProject->implementation_method_id,
            'project_ownership_id' => $originalProject->project_ownership_id,
            'jkkk_name' => $originalProject->jkkk_name,
            'state_government_asset' => $originalProject->state_government_asset,
            'bill_of_quantity' => $originalProject->bill_of_quantity,
            'bill_of_quantity_attachment' => $originalProject->bill_of_quantity_attachment,
            
            // Status
            'status' => 'Waiting for Complete Form',
        ];
        
        // Apply changes from NOC
        if (!empty($nocProjectData['nama_projek_baru'])) {
            $preProjectData['name'] = $nocProjectData['nama_projek_baru'];
        }
        
        if (!empty($nocProjectData['kos_baru'])) {
            // CRITICAL: kos_baru goes to actual_project_cost, NOT total_cost
            $preProjectData['actual_project_cost'] = $nocProjectData['kos_baru'];
            $preProjectData['total_cost'] = $nocProjectData['kos_baru']; // Recalculate total
        }
        
        if (!empty($nocProjectData['agensi_pelaksana_baru'])) {
            // Find agency by name (since form sends agency name, not ID)
            $agency = \App\Models\AgencyCategory::where('name', $nocProjectData['agensi_pelaksana_baru'])->first();
            if ($agency) {
                $preProjectData['implementing_agency_id'] = $agency->id;
                $preProjectData['agency_category_id'] = $agency->id;
            } else {
                Log::warning("Agency not found in NOC, using original agency", [
                    'agency_name' => $nocProjectData['agensi_pelaksana_baru'],
                    'original_agency_id' => $preProjectData['implementing_agency_id']
                ]);
            }
        }
        
        // Create pre-project record
        return PreProject::create($preProjectData);
    }
    
    /**
     * Create pre-project record from new NOC entry (Add New project)
     * 
     * @param Noc $noc
     * @param array $nocProjectData Pivot data from noc_project
     * @return PreProject
     */
    public function createPreProjectFromNewNocEntry(Noc $noc, array $nocProjectData): PreProject
    {
        // Find agency by name
        $agencyId = null;
        if (!empty($nocProjectData['agensi_pelaksana_baru'])) {
            $agency = \App\Models\AgencyCategory::where('name', $nocProjectData['agensi_pelaksana_baru'])->first();
            if ($agency) {
                $agencyId = $agency->id;
            }
        }
        
        $kosBaru = $nocProjectData['kos_baru'] ?? 0;
        
        // Create pre-project with minimal required data
        // CRITICAL: kos_baru goes to actual_project_cost, NOT total_cost
        $preProjectData = [
            'name' => $nocProjectData['nama_projek_baru'] ?? 'New Project',
            'actual_project_cost' => $kosBaru,
            'original_project_cost' => $kosBaru, // Store original for validation
            'consultation_cost' => 0,
            'lss_inspection_cost' => 0,
            'sst' => 0,
            'others_cost' => 0,
            'total_cost' => $kosBaru, // Initially same as actual
            'status' => 'Waiting for Complete Form',
            'parliament_id' => $noc->parliament_id,
            'dun_id' => $noc->dun_id,
            'implementing_agency_id' => $agencyId,
            'agency_category_id' => $agencyId,
        ];
        
        return PreProject::create($preProjectData);
    }
}
