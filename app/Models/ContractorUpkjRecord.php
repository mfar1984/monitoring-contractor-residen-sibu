<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorUpkjRecord extends Model
{
    protected $fillable = [
        'contractor_category_id',
        'category',
        'registration_status',
        'validity_period',
        'validity_from',
        'validity_to',
        'bumiputera_status',
        'bumiputera_validity',
        'bumiputera_from',
        'bumiputera_to',
        'certificate_no',
        'classifications',
    ];

    protected $casts = [
        'classifications' => 'array',
        'validity_from' => 'date',
        'validity_to' => 'date',
        'bumiputera_from' => 'date',
        'bumiputera_to' => 'date',
    ];

    public function contractorCategory()
    {
        return $this->belongsTo(ContractorCategory::class);
    }

    /**
     * Alias for contractorCategory relationship
     */
    public function contractor()
    {
        return $this->belongsTo(ContractorCategory::class, 'contractor_category_id');
    }

    public function getFormattedClassifications(): string
    {
        if (empty($this->classifications)) {
            return 'No classifications';
        }

        return collect($this->classifications)
            ->map(fn($c) => "{$c['class']}-{$c['head_code']}-{$c['subhead_code']}")
            ->join(', ');
    }

    public function hasClassifications(): bool
    {
        return !empty($this->classifications) && count($this->classifications) > 0;
    }

    public function isExpired(): bool
    {
        if ($this->validity_to) {
            return $this->validity_to->isPast();
        }
        
        if ($this->validity_period && strpos($this->validity_period, ' - ') !== false) {
            try {
                $dates = explode(' - ', $this->validity_period);
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dates[1]));
                return $endDate->isPast();
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    public function getFormattedValidityPeriod(): string
    {
        if ($this->validity_from && $this->validity_to) {
            return $this->validity_from->format('d/m/Y') . ' - ' . $this->validity_to->format('d/m/Y');
        }
        
        return $this->validity_period ?? '-';
    }
    
    public function getFormattedBumiValidity(): string
    {
        if ($this->bumiputera_from && $this->bumiputera_to) {
            return $this->bumiputera_from->format('d/m/Y') . ' - ' . $this->bumiputera_to->format('d/m/Y');
        }
        
        return $this->bumiputera_validity ?? '-';
    }
}
