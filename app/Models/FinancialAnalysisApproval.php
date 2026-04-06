<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAnalysisApproval extends Model
{
    protected $fillable = [
        'financial_analysis_id',
        'position',
        'name',
        'department',
        'signature_path',
        'signed_at',
        'display_order',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Financial Analysis
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(FinancialAnalysis::class, 'financial_analysis_id');
    }

    /**
     * Check if signed
     */
    public function isSigned(): bool
    {
        return !is_null($this->signed_at) && !is_null($this->signature_path);
    }
}
