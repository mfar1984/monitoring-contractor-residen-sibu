<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorSelection extends Model
{
    protected $fillable = [
        'selection_number',
        'division_id',
        'district_id',
        'upkj_categories',
        'upkj_classes',
        'upkj_heads',
        'upkj_subheads',
        'created_by',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'upkj_categories' => 'array',
        'upkj_classes' => 'array',
        'upkj_heads' => 'array',
        'upkj_subheads' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contractors()
    {
        return $this->belongsToMany(ContractorCategory::class, 'contractor_selection_contractor')
            ->withPivot([
                'company_name',
                'registration_number',
                'upkj_class',
                'upkj_head',
                'upkj_subhead',
                'upk_expiry_date',
                'status_at_generation',
                'snapshot_data',
            ])
            ->withTimestamps();
    }

    /**
     * Generate unique selection number
     * Format: SEL/YYYY/###
     */
    public static function generateSelectionNumber(): string
    {
        $year = date('Y');
        $lastSelection = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastSelection ? intval(substr($lastSelection->selection_number, -3)) + 1 : 1;

        return 'SEL/' . $year . '/' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}

