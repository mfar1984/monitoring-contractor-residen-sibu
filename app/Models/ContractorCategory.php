<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorCategory extends Model
{
    protected $fillable = [
        'company_name',
        'code',
        'registration_number',
        'office_registration_no',
        'email',
        'telephone_no',
        'mobile_no',
        'fax_no',
        'contact_person',
        'contact_no',
        'date_established',
        'company_type',
        'company_category',
        'registration_category',
        'division',
        'district',
        'registration_status_valid',
        'registration_status_expired',
        'bumiputera_status',
        'rescue_contractor',
        'authorized_person_name',
        'authorized_person_ic',
        'upk_license_no',
        'upk_expiry_date',
        'upkj_class',
        'upkj_head',
        'upkj_subhead',
        'shareholders_data',
        'manpower_sole_proprietor',
        'manpower_management',
        'manpower_professional',
        'manpower_sub_professional',
        'manpower_competent_worker',
        'manpower_total',
        'registered_address',
        'postal_address',
        'business_address',
        'registered_location',
        'registered_address_city',
        'registered_address_state',
        'registered_address_postcode',
        'description',
        'status',
    ];

    protected $casts = [
        'date_established' => 'date',
        'upk_expiry_date' => 'date',
        'registration_status_valid' => 'boolean',
        'registration_status_expired' => 'boolean',
        'rescue_contractor' => 'boolean',
        'shareholders_data' => 'array',
        'manpower_sole_proprietor' => 'integer',
        'manpower_management' => 'integer',
        'manpower_professional' => 'integer',
        'manpower_sub_professional' => 'integer',
        'manpower_competent_worker' => 'integer',
        'manpower_total' => 'integer',
    ];

    /**
     * Calculate total manpower automatically
     */
    public function calculateTotalManpower(): int
    {
        return ($this->manpower_sole_proprietor ?? 0) +
               ($this->manpower_management ?? 0) +
               ($this->manpower_professional ?? 0) +
               ($this->manpower_sub_professional ?? 0) +
               ($this->manpower_competent_worker ?? 0);
    }

    /**
     * Get shareholders as array
     */
    public function getShareholders(): array
    {
        return $this->shareholders_data ?? [];
    }

    /**
     * Set shareholders data
     */
    public function setShareholders(array $shareholders): void
    {
        $this->shareholders_data = $shareholders;
    }

    /**
     * Get UPKJ classifications from upkj_classifications table
     */
    public static function getUpkjClasses()
    {
        return \App\Models\UpkjClassification::select('class', 'class_description')
            ->distinct()
            ->orderBy('class')
            ->get();
    }

    /**
     * Get UPKJ heads for a specific class
     */
    public static function getUpkjHeads($class = null)
    {
        $query = \App\Models\UpkjClassification::select('head_code', 'head_name')
            ->distinct();
        
        if ($class) {
            $query->where('class', $class);
        }
        
        return $query->orderBy('head_code')->get();
    }

    /**
     * Get UPKJ subheads for a specific class and head
     */
    public static function getUpkjSubheads($class = null, $head = null)
    {
        $query = \App\Models\UpkjClassification::select('subhead_code', 'subhead_letter', 'subhead_roman')
            ->whereNotNull('subhead_code')
            ->distinct();
        
        if ($class) {
            $query->where('class', $class);
        }
        
        if ($head) {
            $query->where('head_code', $head);
        }
        
        return $query->orderBy('subhead_code')->get();
    }

    /**
     * Relationship: Users belonging to this contractor category
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'contractor_category_id');
    }
    /**
     * Relationship: Has many UPKJ records
     */
    public function upkjRecords()
    {
        return $this->hasMany(ContractorUpkjRecord::class);
    }

    /**
     * Get all UPKJ records with classifications
     */
    public function getUpkjRecordsWithClassifications()
    {
        return $this->upkjRecords()
            ->orderBy('created_at', 'desc')
            ->get();
    }


}
