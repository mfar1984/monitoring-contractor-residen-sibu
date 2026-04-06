<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAnalysisContractor extends Model
{
    protected $fillable = [
        'financial_analysis_id',
        'contractor_category_id',
        'contractor_name',
        'registration_number',
        'contractor_class',
        'registration_validity_date',
        'upkj_classifications',
        'current_contract_load',
        'performance_record',
        'minimum_capital_requirement',
        'fixed_deposit',
        'credit_facility_balance',
        'additional_credit_facility',
        'three_month_average',
        'meeting_decision',
        'justification',
        'is_qualified',
        'remarks',
        'display_order',
    ];

    protected $casts = [
        'registration_validity_date' => 'date',
        'current_contract_load' => 'decimal:2',
        'minimum_capital_requirement' => 'decimal:2',
        'fixed_deposit' => 'decimal:2',
        'credit_facility_balance' => 'decimal:2',
        'additional_credit_facility' => 'decimal:2',
        'three_month_average' => 'decimal:2',
        'is_qualified' => 'boolean',
    ];

    /**
     * Relationship: Belongs to Financial Analysis
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(FinancialAnalysis::class, 'financial_analysis_id');
    }

    /**
     * Relationship: Belongs to Contractor Category
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(ContractorCategory::class, 'contractor_category_id');
    }

    /**
     * Relationship: Has many bank statements
     */
    public function bankStatements(): HasMany
    {
        return $this->hasMany(FinancialAnalysisBankStatement::class)
            ->orderBy('month_date');
    }

    /**
     * Auto-populate contractor basic information
     */
    public function populateFromContractor(ContractorCategory $contractor): void
    {
        $this->contractor_name = $contractor->company_name;
        $this->registration_number = $contractor->registration_number;
        
        // Get UPKJ classifications from upkjRecords
        $upkjRecords = $contractor->upkjRecords;
        if ($upkjRecords->isNotEmpty()) {
            $classifications = [];
            $firstClass = null;
            
            foreach ($upkjRecords as $record) {
                // Get classifications from JSON array
                $recordClassifications = $record->classifications ?? [];
                
                foreach ($recordClassifications as $classification) {
                    // Get class from first classification
                    if (!$firstClass && !empty($classification['class'])) {
                        $firstClass = $classification['class'];
                    }
                    
                    // Build classification string
                    $class = $classification['class'] ?? '';
                    $headCode = $classification['head_code'] ?? '';
                    $subheadCode = $classification['subhead_code'] ?? '';
                    
                    if (!empty($class) && !empty($headCode) && !empty($subheadCode)) {
                        $classifications[] = "{$class}{$headCode}{$subheadCode}";
                    } elseif (!empty($class) && !empty($headCode)) {
                        $classifications[] = "{$class}{$headCode}";
                    } elseif (!empty($class)) {
                        $classifications[] = $class;
                    }
                }
            }
            
            $this->upkj_classifications = !empty($classifications) ? implode(', ', array_unique($classifications)) : 'N/A';
            $this->contractor_class = $firstClass ?? 'N/A';
        } else {
            // No UPKJ records found
            $this->upkj_classifications = 'N/A';
            $this->contractor_class = 'N/A';
        }
    }

    /**
     * Calculate 3-month average from bank statements
     */
    public function calculateThreeMonthAverage(): float
    {
        $statements = $this->bankStatements()
            ->whereNotNull('ending_balance')
            ->orderBy('month_date', 'desc')
            ->limit(3)
            ->get();
        
        if ($statements->isEmpty()) {
            return 0;
        }
        
        $sum = $statements->sum('ending_balance');
        $count = $statements->count();
        
        return round($sum / $count, 2);
    }

    /**
     * Check if registration is expired
     */
    public function isRegistrationExpired(): bool
    {
        if (!$this->registration_validity_date) {
            return false;
        }
        
        return $this->registration_validity_date->isPast();
    }

    /**
     * Get total financial capacity
     */
    public function getTotalFinancialCapacity(): float
    {
        return ($this->three_month_average ?? 0) +
               ($this->fixed_deposit ?? 0) +
               ($this->credit_facility_balance ?? 0) +
               ($this->additional_credit_facility ?? 0);
    }
}
