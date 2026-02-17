<?php

namespace App\Services;

use App\Models\PreProject;
use App\Models\Project;
use Exception;

class ProjectTransferService
{
    /**
     * Transfer an approved pre-project to projects table
     * 
     * @param PreProject $preProject
     * @param string $projectNumber
     * @param string $projectYear
     * @return Project
     * @throws Exception if pre-project not approved
     */
    public function transfer(PreProject $preProject, string $projectNumber, string $projectYear): Project
    {
        // 1. Check if already transferred
        $existing = $this->getProjectForPreProject($preProject);
        if ($existing) {
            // Update status if not yet approved
            if ($preProject->status !== 'Approved') {
                $preProject->update(['status' => 'Approved']);
            }
            return $existing;
        }
        
        // 2. Copy all data from pre-project
        $projectData = $preProject->toArray();
        unset($projectData['id'], $projectData['created_at'], $projectData['updated_at']);
        
        // 3. Add project-specific fields
        $projectData['project_number'] = $projectNumber;
        $projectData['project_year'] = $projectYear;
        $projectData['pre_project_id'] = $preProject->id;
        $projectData['approval_date'] = now();
        $projectData['transferred_at'] = now();
        $projectData['status'] = 'Active';
        
        // 4. Create project record
        $project = Project::create($projectData);
        
        // 5. Update pre-project status to Approved
        $preProject->refresh(); // Refresh to get latest data
        $preProject->status = 'Approved';
        $preProject->save();
        
        \Log::info('Pre-Project transferred successfully', [
            'pre_project_id' => $preProject->id,
            'project_id' => $project->id,
            'pre_project_status' => $preProject->status
        ]);
        
        return $project;
    }
    
    /**
     * Check if pre-project can be transferred
     * 
     * @param PreProject $preProject
     * @return bool
     */
    public function canTransfer(PreProject $preProject): bool
    {
        // Check if not already transferred
        if ($this->getProjectForPreProject($preProject)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get project for a pre-project (if already transferred)
     * 
     * @param PreProject $preProject
     * @return Project|null
     */
    public function getProjectForPreProject(PreProject $preProject): ?Project
    {
        return Project::where('pre_project_id', $preProject->id)->first();
    }

}
