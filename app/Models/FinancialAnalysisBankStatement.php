<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAnalysisBankStatement extends Model
{
    protected $fillable = [
        'financial_analysis_contractor_id',
        'month_year',
        'month_date',
        'ending_balance',
        'display_order',
    ];

    protected $casts = [
        'month_date' => 'date',
        'ending_balance' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to Financial Analysis Contractor
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(FinancialAnalysisContractor::class, 'financial_analysis_contractor_id');
    }

    /**
     * Format month year for display
     */
    public function getFormattedMonthYear(): string
    {
        return $this->month_date->format('M-y');
    }
}
