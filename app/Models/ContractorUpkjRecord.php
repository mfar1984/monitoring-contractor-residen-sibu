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
        'bumiputera_status',
        'bumiputera_validity',
        'certificate_no',
        'classifications',
    ];

    protected $casts = [
        'classifications' => 'array',
    ];

    /**
     * Relationship: Belongs to contractor category
     */
    public function contractorCategory()
    {
        return $this->belongsTo(ContractorCategory::class);
    }

    /**
     * Get formatted classifications for display
     */
    public function getFormattedClassifications(): string
    {
        if (empty($this->classifications)) {
            return 'No classifications';
        }

        return collect($this->classifications)
            ->map(fn($c) => "{$c['class']}-{$c['head_code']}-{$c['subhead_code']}")
            ->join(', ');
    }

    /**
     * Validate that at least one classification exists
     */
    public function hasClassifications(): bool
    {
        return !empty($this->classifications) && count($this->classifications) > 0;
    }
}
