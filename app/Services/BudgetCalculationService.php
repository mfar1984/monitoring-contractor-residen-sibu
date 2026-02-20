<?php

namespace App\Services;

use App\Models\User;
use App\Models\Parliament;
use App\Models\Dun;
use App\Models\PreProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetCalculationService
{
    /**
     * Get budget data for a Parliament/DUN user
     * 
     * @param User $user
     * @param int|null $year (defaults to current year)
     * @return array|null ['total_budget', 'total_allocated', 'remaining_budget', 'year', 'parliament_id', 'dun_id', 'source_name']
     */
    public function getUserBudgetData(User $user, ?int $year = null): ?array
    {
        try {
            $year = $year ?? date('Y');
            
            // Check if user is Parliament or DUN user
            if (!$user->parliament_category_id && !$user->dun_id) {
                // Return empty budget info for non-Parliament/DUN users
                return [
                    'total_budget' => 0.0,
                    'total_allocated' => 0.0,
                    'remaining_budget' => 0.0,
                    'year' => $year,
                    'parliament_id' => null,
                    'dun_id' => null,
                    'source_name' => '',
                ];
            }
            
            $totalBudget = 0;
            $totalAllocated = 0;
            $parliamentId = null;
            $dunId = null;
            $sourceName = '';
            
            // Check if user has parliament_category_id (Parliament user)
            if ($user->parliament_category_id) {
                $parliament = Parliament::find($user->parliament_category_id);
                
                if ($parliament) {
                    // Get budget from parliament_budgets table for the year
                    $budgetRecord = DB::table('parliament_budgets')
                        ->where('parliament_id', $parliament->id)
                        ->where('year', $year)
                        ->first();
                    
                    $totalBudget = $budgetRecord ? $budgetRecord->budget : 0;
                    $parliamentId = $parliament->id;
                    $sourceName = $parliament->name;
                    
                    // Calculate total allocated for this Parliament in this year
                    // Exclude pre-projects that have been cancelled, rejected, or in NOC
                    $totalAllocated = PreProject::where('parliament_id', $parliamentId)
                        ->where('project_year', $year)
                        ->whereNotIn('status', ['Cancelled', 'Rejected', 'NOC'])
                        ->whereDoesntHave('project', function($query) {
                            $query->where('status', 'Projek Dibatalkan');
                        })
                        ->sum('total_cost');
                }
            }
            
            // Check if user has dun_id (DUN user)
            if ($user->dun_id) {
                $dun = Dun::find($user->dun_id);
                
                if ($dun) {
                    // Get budget from dun_budgets table for the year
                    $budgetRecord = DB::table('dun_budgets')
                        ->where('dun_id', $dun->id)
                        ->where('year', $year)
                        ->first();
                    
                    $totalBudget = $budgetRecord ? $budgetRecord->budget : 0;
                    $dunId = $dun->id;
                    $sourceName = $dun->name;
                    
                    // Calculate total allocated for this DUN in this year
                    // Exclude pre-projects that have been cancelled, rejected, or in NOC
                    $totalAllocated = PreProject::where('dun_id', $dunId)
                        ->where('project_year', $year)
                        ->whereNotIn('status', ['Cancelled', 'Rejected', 'NOC'])
                        ->whereDoesntHave('project', function($query) {
                            $query->where('status', 'Projek Dibatalkan');
                        })
                        ->sum('total_cost');
                }
            }
            
            if (!$parliamentId && !$dunId) {
                Log::warning('User has parliament_category_id or dun_id but no matching record found', [
                    'user_id' => $user->id,
                    'parliament_category_id' => $user->parliament_category_id,
                    'dun_id' => $user->dun_id
                ]);
                return [
                    'total_budget' => 0.0,
                    'total_allocated' => 0.0,
                    'remaining_budget' => 0.0,
                    'year' => $year,
                    'parliament_id' => null,
                    'dun_id' => null,
                    'source_name' => '',
                ];
            }
            
            $remainingBudget = $totalBudget - $totalAllocated;
            
            return [
                'total_budget' => (float) $totalBudget,
                'total_allocated' => (float) $totalAllocated,
                'remaining_budget' => (float) $remainingBudget,
                'year' => $year,
                'parliament_id' => $parliamentId,
                'dun_id' => $dunId,
                'source_name' => $sourceName,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate user budget data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'total_budget' => 0.0,
                'total_allocated' => 0.0,
                'remaining_budget' => 0.0,
                'year' => $year ?? date('Y'),
                'parliament_id' => null,
                'dun_id' => null,
                'source_name' => '',
            ];
        }
    }
    /**
     * Get budget information for Parliament/DUN users
     *
     * This is an alias for getUserBudgetData() for backward compatibility
     *
     * @param User $user
     * @param int|null $year (defaults to current year)
     * @return array|null ['total_budget', 'total_allocated', 'remaining_budget', 'year', 'parliament_id', 'dun_id']
     */
    public function getUserBudgetInfo(User $user, ?int $year = null): ?array
    {
        return $this->getUserBudgetData($user, $year);
    }

    
    /**
     * Get aggregated budget data for Residen users
     * 
     * @param int|null $year (defaults to current year)
     * @return array ['total_parliament_budget', 'total_dun_budget', 'total_allocated', 'overall_remaining', 'year']
     */
    public function getResidenBudgetOverview(?int $year = null): array
    {
        try {
            $year = $year ?? date('Y');
            
            // Sum all Parliament budgets
            $totalParliamentBudget = Parliament::where('status', 'Active')->sum('budget') ?? 0;
            
            // Sum all DUN budgets
            $totalDunBudget = Dun::where('status', 'Active')->sum('budget') ?? 0;
            
            // Sum all allocated pre-projects
            $totalAllocated = PreProject::whereIn('status', ['Waiting for Approval', 'Approved'])
                ->sum('total_cost') ?? 0;
            
            $overallRemaining = ($totalParliamentBudget + $totalDunBudget) - $totalAllocated;
            
            return [
                'total_parliament_budget' => (float) $totalParliamentBudget,
                'total_dun_budget' => (float) $totalDunBudget,
                'total_allocated' => (float) $totalAllocated,
                'overall_remaining' => (float) $overallRemaining,
                'year' => $year,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate Residen budget overview', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_parliament_budget' => 0.0,
                'total_dun_budget' => 0.0,
                'total_allocated' => 0.0,
                'overall_remaining' => 0.0,
                'year' => $year ?? date('Y'),
            ];
        }
    }
    
    /**
     * Calculate if a pre-project would exceed budget
     * 
     * @param User $user
     * @param float $projectCost
     * @param int|null $excludePreProjectId (for edit scenarios)
     * @param int|null $year (defaults to current year)
     * @return bool
     */
    public function wouldExceedBudget(User $user, float $projectCost, ?int $excludePreProjectId = null, ?int $year = null): bool
    {
        // Residen users are not subject to budget validation
        if (!$this->isSubjectToBudgetValidation($user)) {
            return false;
        }
        
        $year = $year ?? date('Y');
        $budgetData = $this->getUserBudgetData($user, $year);
        
        if (!$budgetData) {
            return false; // No budget data, allow creation
        }
        
        $currentAllocated = $budgetData['total_allocated'];
        
        // If editing, exclude the current pre-project cost
        if ($excludePreProjectId) {
            $existingProject = PreProject::find($excludePreProjectId);
            if ($existingProject) {
                $currentAllocated -= $existingProject->total_cost;
            }
        }
        
        $newTotalAllocated = $currentAllocated + $projectCost;
        
        return $newTotalAllocated > $budgetData['total_budget'];
    }
    
    /**
     * Get remaining budget after hypothetical pre-project
     * 
     * @param User $user
     * @param float $projectCost
     * @param int|null $excludePreProjectId
     * @param int|null $year (defaults to current year)
     * @return float
     */
    public function getRemainingBudgetAfter(User $user, float $projectCost, ?int $excludePreProjectId = null, ?int $year = null): float
    {
        $year = $year ?? date('Y');
        $budgetData = $this->getUserBudgetData($user, $year);
        
        if (!$budgetData) {
            return 0.0;
        }
        
        $currentAllocated = $budgetData['total_allocated'];
        
        // If editing, exclude the current pre-project cost
        if ($excludePreProjectId) {
            $existingProject = PreProject::find($excludePreProjectId);
            if ($existingProject) {
                $currentAllocated -= $existingProject->total_cost;
            }
        }
        
        $newTotalAllocated = $currentAllocated + $projectCost;
        
        return $budgetData['total_budget'] - $newTotalAllocated;
    }
    
    /**
     * Check if user is subject to budget validation
     * 
     * @param User $user
     * @return bool
     */
    public function isSubjectToBudgetValidation(User $user): bool
    {
        // Only Parliament/DUN users are subject to budget validation
        // Residen/Admin users are exempt
        return $user->parliament_category_id !== null && $user->residen_category_id === null;
    }

    /**
     * Get aggregated budget information for Residen users
     * 
     * Calculates total budgets and allocations across all Parliament and DUN
     * constituencies for a specific fiscal year.
     * 
     * @param User $user The authenticated Residen user
     * @param int|null $year The fiscal year (defaults to current year)
     * @return array [
     *   'total_budget_parliament' => float,
     *   'total_budget_dun' => float,
     *   'total_allocated_parliament' => float,
     *   'total_allocated_dun' => float,
     *   'remaining_budget' => float,
     *   'year' => int
     * ]
     */
    public function getResidenBudgetInfo(User $user, ?int $year = null): array
    {
        // Default to current year if not specified
        $year = $year ?? now()->year;
        
        try {
            // Calculate total Parliament budgets
            $totalBudgetParliament = DB::table('parliament_budgets')
                ->where('year', $year)
                ->sum('budget');
            
            // Calculate total DUN budgets
            $totalBudgetDun = DB::table('dun_budgets')
                ->where('year', $year)
                ->sum('budget');
            
            // Calculate total allocated to Parliament pre-projects
            // Exclude pre-projects that have been cancelled, rejected, or in NOC
            $totalAllocatedParliament = PreProject::whereNotNull('parliament_id')
                ->where('project_year', $year)
                ->whereNotIn('status', ['Cancelled', 'Rejected', 'NOC'])
                ->whereDoesntHave('project', function($query) {
                    $query->where('status', 'Projek Dibatalkan');
                })
                ->sum('total_cost');
            
            // Calculate total allocated to DUN pre-projects
            // Exclude pre-projects that have been cancelled, rejected, or in NOC
            $totalAllocatedDun = PreProject::whereNotNull('dun_id')
                ->where('project_year', $year)
                ->whereNotIn('status', ['Cancelled', 'Rejected', 'NOC'])
                ->whereDoesntHave('project', function($query) {
                    $query->where('status', 'Projek Dibatalkan');
                })
                ->sum('total_cost');
            
            // Calculate remaining budget
            $totalBudget = $totalBudgetParliament + $totalBudgetDun;
            $totalAllocated = $totalAllocatedParliament + $totalAllocatedDun;
            $remainingBudget = $totalBudget - $totalAllocated;
            
            return [
                'total_budget_parliament' => (float) ($totalBudgetParliament ?? 0),
                'total_budget_dun' => (float) ($totalBudgetDun ?? 0),
                'total_allocated_parliament' => (float) ($totalAllocatedParliament ?? 0),
                'total_allocated_dun' => (float) ($totalAllocatedDun ?? 0),
                'remaining_budget' => $remainingBudget,
                'year' => $year,
            ];
        } catch (\Exception $e) {
            Log::error('Residen budget calculation failed', [
                'user_id' => $user->id,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return [
                'total_budget_parliament' => 0.0,
                'total_budget_dun' => 0.0,
                'total_allocated_parliament' => 0.0,
                'total_allocated_dun' => 0.0,
                'remaining_budget' => 0.0,
                'year' => $year,
            ];
        }
    }
}
