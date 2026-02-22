<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContractorAnalysisTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'created_by',
        'agency_category_id',
        'attachment_path',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(AgencyCategory::class, 'agency_category_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'contractor_analysis_project')
            ->withTimestamps();
    }

    // Helper Methods
    public static function generateTransferNumber(): string
    {
        $year = date('Y');
        $prefix = "CA/{$year}/";
        
        $lastTransfer = self::where('transfer_number', 'LIKE', $prefix . '%')
            ->orderBy('transfer_number', 'desc')
            ->first();
        
        if (!$lastTransfer) {
            return $prefix . '001';
        }
        
        $lastNumber = (int) substr($lastTransfer->transfer_number, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public static function getAvailableProjects(User $user)
    {
        $query = Project::where('status', 'Active');
        
        // Apply agency filtering for Agency users
        if ($user->agency_category_id) {
            $query->where('agency_category_id', $user->agency_category_id);
        }
        // Residen users see all available projects (no filter)
        
        return $query->with(['agencyCategory', 'parliament', 'dun'])
            ->orderBy('project_number')
            ->get();
    }
}
