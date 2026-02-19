<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'residen_category_id',
        'agency_category_id',
        'parliament_id',
        'dun_basic_id',
        'project_category_id',
        'project_scope',
        'actual_project_cost',
        'consultation_cost',
        'lss_inspection_cost',
        'sst',
        'others_cost',
        'total_cost',
        'original_project_cost',
        'implementation_period',
        'division_id',
        'district_id',
        'parliament_location_id',
        'dun_id',
        'site_layout',
        'land_title_status_id',
        'consultation_service',
        'implementing_agency_id',
        'implementation_method_id',
        'project_ownership_id',
        'jkkk_name',
        'state_government_asset',
        'bill_of_quantity',
        'bill_of_quantity_attachment',
        'status',
        'submitted_to_epu_at',
        'submitted_to_epu_by',
        'first_approver_id',
        'first_approved_at',
        'first_approval_remarks',
        'second_approver_id',
        'second_approved_at',
        'second_approval_remarks',
        'rejection_remarks',
        'rejected_by',
        'rejected_at',
    ];

    /**
     * Get the residen category that owns the pre-project.
     */
    public function residenCategory()
    {
        return $this->belongsTo(ResidenCategory::class);
    }

    /**
     * Get the agency category that owns the pre-project.
     */
    public function agencyCategory()
    {
        return $this->belongsTo(AgencyCategory::class);
    }

    /**
     * Get the parliament that owns the pre-project.
     */
    public function parliament()
    {
        return $this->belongsTo(Parliament::class);
    }

    /**
     * Get the DUN for basic information.
     */
    public function dunBasic()
    {
        return $this->belongsTo(Dun::class, 'dun_basic_id');
    }

    /**
     * Get the project category that owns the pre-project.
     */
    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    /**
     * Get the division for project location.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the district for project location.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the parliament for project location.
     */
    public function parliamentLocation()
    {
        return $this->belongsTo(Parliament::class, 'parliament_location_id');
    }

    /**
     * Get the DUN for project location.
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }

    /**
     * Get the land title status.
     */
    public function landTitleStatus()
    {
        return $this->belongsTo(LandTitleStatus::class);
    }

    /**
     * Get the parliament for project ownership.
     */
    public function projectOwnershipParliament()
    {
        return $this->belongsTo(Parliament::class, 'project_ownership_parliament_id');
    }

    /**
     * Get the implementing agency.
     */
    public function implementingAgency()
    {
        return $this->belongsTo(AgencyCategory::class, 'implementing_agency_id');
    }

    /**
     * Get the implementation method.
     */
    public function implementationMethod()
    {
        return $this->belongsTo(ImplementationMethod::class);
    }

    /**
     * Get the project ownership.
     */
    public function projectOwnership()
    {
        return $this->belongsTo(ProjectOwnership::class);
    }

    /**
     * Get all NOCs through the project (if this pre-project has been transferred)
     * Note: NOCs are now associated with Projects, not Pre-Projects
     */
    public function nocs()
    {
        // If this pre-project has been transferred to a project, get NOCs through the project
        if ($this->project) {
            return $this->project->nocs();
        }
        
        // Return empty relationship if no project exists
        return $this->belongsToMany(Noc::class, 'noc_project', 'project_id', 'noc_id')->whereRaw('1 = 0');
    }

    /**
     * Get the project if this pre-project has been transferred
     */
    public function project()
    {
        return $this->hasOne(Project::class, 'pre_project_id');
    }

    /**
     * Get the first approver user
     */
    public function firstApprover()
    {
        return $this->belongsTo(User::class, 'first_approver_id');
    }

    /**
     * Get the second approver user
     */
    public function secondApprover()
    {
        return $this->belongsTo(User::class, 'second_approver_id');
    }

    /**
     * Get the user who rejected this pre-project
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the user who submitted to EPU
     */
    public function submittedToEpuBy()
    {
        return $this->belongsTo(User::class, 'submitted_to_epu_by');
    }

    /**
     * Get the list of required fields for EPU submission
     * 
     * @return array Array of field names and their display labels
     */
    public function getRequiredFieldsDefinition(): array
    {
        return [
            'project_scope' => 'Project Scope',
            'project_category_id' => 'Project Category',
            'implementation_period' => 'Implementation Period',
            'division_id' => 'Division',
            'district_id' => 'District',
            'land_title_status_id' => 'Land Title Status',
            'implementing_agency_id' => 'Implementing Agency',
            'implementation_method_id' => 'Implementation Method',
            'project_ownership_id' => 'Project Ownership',
        ];
    }

    /**
     * Calculate data completeness percentage
     * 
     * @return int Percentage from 0 to 100
     */
    public function getCompletenessPercentage(): int
    {
        $requiredFields = array_keys($this->getRequiredFieldsDefinition());
        $totalFields = count($requiredFields);
        
        if ($totalFields === 0) {
            return 100;
        }
        
        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $filledFields++;
            }
        }
        
        return (int) round(($filledFields / $totalFields) * 100);
    }

    /**
     * Get array of missing required fields with display names
     * 
     * @return array Array of missing field display names
     */
    public function getMissingRequiredFields(): array
    {
        $requiredFields = $this->getRequiredFieldsDefinition();
        $missingFields = [];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($this->$field)) {
                $missingFields[] = $label;
            }
        }
        
        return $missingFields;
    }

    /**
     * Check if Pre-Project data is complete for EPU submission
     * 
     * @return bool True if all required fields are filled
     */
    public function isDataComplete(): bool
    {
        return $this->getCompletenessPercentage() === 100;
    }

    /**
     * Get completeness badge color based on percentage
     * 
     * @return string CSS color code
     */
    public function getCompletenessBadgeColor(): string
    {
        $percentage = $this->getCompletenessPercentage();
        
        if ($percentage >= 81) {
            return '#28a745'; // Green
        } elseif ($percentage >= 51) {
            return '#ffc107'; // Yellow
        } else {
            return '#dc3545'; // Red
        }
    }

    /**
     * Calculate total cost from all cost components
     * 
     * @return float Total cost
     */
    public function calculateTotalCost(): float
    {
        return ($this->actual_project_cost ?? 0) + 
               ($this->consultation_cost ?? 0) + 
               ($this->lss_inspection_cost ?? 0) + 
               ($this->sst ?? 0) + 
               ($this->others_cost ?? 0);
    }

    /**
     * Check if actual project cost is within original budget
     * 
     * @return bool True if within budget
     */
    public function isWithinBudget(): bool
    {
        // If no original cost set, allow any amount
        if (empty($this->original_project_cost)) {
            return true;
        }
        
        return ($this->actual_project_cost ?? 0) <= $this->original_project_cost;
    }

    /**
     * Get the budget difference (remaining or exceeded)
     * 
     * @return float Positive = remaining, Negative = exceeded
     */
    public function getBudgetDifference(): float
    {
        if (empty($this->original_project_cost)) {
            return 0;
        }
        
        return $this->original_project_cost - ($this->actual_project_cost ?? 0);
    }
}
