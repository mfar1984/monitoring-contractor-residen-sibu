<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        // Project identification
        'project_number',
        'project_year',
        'pre_project_id',
        'approval_date',
        'transferred_at',
        
        // All fields from PreProject model
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

    protected $casts = [
        'approval_date' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    /**
     * Get the original pre-project
     */
    public function preProject()
    {
        return $this->belongsTo(PreProject::class);
    }

    /**
     * Get all NOCs that include this project
     */
    public function nocs()
    {
        return $this->belongsToMany(Noc::class, 'noc_project')
            ->withPivot([
                'tahun_rtp',
                'no_projek',
                'nama_projek_asal',
                'nama_projek_baru',
                'kos_asal',
                'kos_baru',
                'agensi_pelaksana_asal',
                'agensi_pelaksana_baru',
                'noc_note_id'
            ])
            ->withTimestamps();
    }

    /**
     * Get the residen category
     */
    public function residenCategory()
    {
        return $this->belongsTo(ResidenCategory::class);
    }

    /**
     * Get the agency category
     */
    public function agencyCategory()
    {
        return $this->belongsTo(AgencyCategory::class);
    }

    /**
     * Get the parliament
     */
    public function parliament()
    {
        return $this->belongsTo(Parliament::class);
    }

    /**
     * Get the DUN for basic information
     */
    public function dunBasic()
    {
        return $this->belongsTo(Dun::class, 'dun_basic_id');
    }

    /**
     * Get the project category
     */
    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    /**
     * Get the division for project location
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the district for project location
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the parliament for project location
     */
    public function parliamentLocation()
    {
        return $this->belongsTo(Parliament::class, 'parliament_location_id');
    }

    /**
     * Get the DUN for project location
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }

    /**
     * Get the land title status
     */
    public function landTitleStatus()
    {
        return $this->belongsTo(LandTitleStatus::class);
    }

    /**
     * Get the implementing agency
     */
    public function implementingAgency()
    {
        return $this->belongsTo(AgencyCategory::class, 'implementing_agency_id');
    }

    /**
     * Get the implementation method
     */
    public function implementationMethod()
    {
        return $this->belongsTo(ImplementationMethod::class);
    }

    /**
     * Get the project ownership
     */
    public function projectOwnership()
    {
        return $this->belongsTo(ProjectOwnership::class);
    }

    /**
     * Generate unique project number
     * Format: PROJ/YYYY/###
     */
    public static function generateProjectNumber(): string
    {
        $year = date('Y');
        $lastProject = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastProject ? intval(substr($lastProject->project_number, -3)) + 1 : 1;
        
        return 'PROJ/' . $year . '/' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: Filter by parliament
     */
    public function scopeForParliament($query, $parliamentId)
    {
        return $query->where('parliament_id', $parliamentId);
    }

    /**
     * Scope: Filter by DUN
     */
    public function scopeForDun($query, $dunId)
    {
        return $query->where('dun_id', $dunId);
    }

    /**
     * Scope: Filter by user access
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->parliament_id) {
            return $query->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            return $query->where('dun_id', $user->dun_id);
        }
        
        return $query;
    }
}
