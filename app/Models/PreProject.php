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
     * Get all NOCs that include this pre-project
     */
    public function nocs()
    {
        return $this->belongsToMany(Noc::class, 'noc_pre_project');
    }

    /**
     * Get the project if this pre-project has been transferred
     */
    public function project()
    {
        return $this->hasOne(Project::class);
    }
}
