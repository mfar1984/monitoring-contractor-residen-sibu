<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAnalysis extends Model
{
    protected $fillable = [
        'contractor_analysis_transfer_id',
        'project_number',
        'project_name',
        'agency_name',
        'department_budget',
        'district_name',
        'project_class',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_remarks',
    ];

    protected $casts = [
        'department_budget' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Contractor Analysis Transfer
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(ContractorAnalysisTransfer::class, 'contractor_analysis_transfer_id');
    }

    /**
     * Relationship: Belongs to creator user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Belongs to submitter user
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship: Belongs to approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Belongs to rejector user
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Relationship: Has many contractor evaluations
     */
    public function contractors(): HasMany
    {
        return $this->hasMany(FinancialAnalysisContractor::class)
            ->orderBy('display_order');
    }

    /**
     * Relationship: Has many approval committee members
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(FinancialAnalysisApproval::class)
            ->orderBy('display_order');
    }

    /**
     * Auto-populate project information from transfer
     */
    public function populateFromTransfer(ContractorAnalysisTransfer $transfer): void
    {
        $project = $transfer->projects()->first();
        
        if ($project) {
            $this->project_number = $project->project_number;
            $this->project_name = $project->name;
            $this->agency_name = $project->agencyCategory->name ?? null;
            $this->department_budget = $project->total_cost;
            $this->district_name = $project->district->name ?? null;
            $this->project_class = $project->projectCategory->name ?? null;
        }
    }

    /**
     * Get total count of contractors evaluated
     */
    public function getContractorCount(): int
    {
        return $this->contractors()->count();
    }

    /**
     * Get count of qualified contractors
     */
    public function getQualifiedCount(): int
    {
        return $this->contractors()->where('is_qualified', true)->count();
    }

    /**
     * Get count of not qualified contractors
     */
    public function getNotQualifiedCount(): int
    {
        return $this->contractors()->where('is_qualified', false)->count();
    }

    /**
     * Check if analysis can be edited
     */
    public function canEdit(): bool
    {
        return $this->status === 'Draft';
    }

    /**
     * Check if analysis can be deleted
     */
    public function canDelete(): bool
    {
        return $this->status === 'Draft';
    }

    /**
     * Check if analysis can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->status === 'Draft' && $this->contractors()->count() > 0;
    }

    /**
     * Check if analysis can be approved
     */
    public function canApprove(): bool
    {
        return $this->status === 'Submitted';
    }
}
