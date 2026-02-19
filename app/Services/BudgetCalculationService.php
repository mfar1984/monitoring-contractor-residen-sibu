<?php

namespace App\Services;

use App\Models\User;
use App\Models\Parliament;
use App\Models\Dun;
use App\Models\PreProject;
use Illuminate\Support\Facades\Log;

class BudgetCalculationService
{
    /**
     * Get budget data for a Parliament/DUN user
     * 
     * @param User $user
     * @param int|null $year (defaults to current year)
     * @return array|null ['total_budget', 'total_allocated', 'remaining_budget', 'year', 'parliament_id', 'dun_id']
     */
    public function getUserBudgetData(User $user, ?int $year = null): ?array
    {
        try {
            $year = $year ?? date('Y');
            
            // Check if user is Parliament or DUN user
            if (!$user->parliament_category_id) {
                return null;
            }
            
            // Determine if Parliament or DUN
            $parliament = Parliament::find($user->parliament_category_id);
            $dun = Dun::find($user->parliament_category_id);
            
            $totalBudget = 0;
            $parliamentId = null;
            $dunId = null;
            
            if ($parliament) {
                $totalBudget = $parliament->budget ?? 0;
                $parliamentId = $parliament->id;
                
                // Calculate total allocated for this Parliament
                $totalAllocated = PreProject::where('parliament_id', $parliamentId)
                    ->whereIn('status', ['Waiting for Approval', 'Approved'])
                    ->sum('total_cost');
            } elseif ($dun) {
                $totalBudget = $dun->budget ?? 0;
                $dunId = $dun->id;
                
                // Calculate total allocated for this DUN
                $totalAllocated = PreProject::where('dun_id', $dunId)
                    ->whereIn('status', ['Waiting for Approval', 'Approved'])
                    ->sum('total_cost');
            } else {
                Log::warning('User has parliament_category_id but no matching Parliament or DUN found', [
                    'user_id' => $user->id,
                    'parliament_category_id' => $user->parliament_category_id
                ]);
                return null;
            }
            
            $remainingBudget = $totalBudget - $totalAllocated;
            
            return [
                'total_budget' => (float) $totalBudget,
                'total_allocated' => (float) $totalAllocated,
                'remaining_budget' => (float) $remainingBudget,
                'year' => $year,
                'parliament_id' => $parliamentId,
                'dun_id' => $dunId,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate user budget data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_budget' => 0.0,
                'total_allocated' => 0.0,
                'remaining_budget' => 0.0,
                'year' => $year ?? date('Y'),
                'parliament_id' => null,
                'dun_id' => null,
            ];
        }
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
     * @return bool
     */
    public function wouldExceedBudget(User $user, float $projectCost, ?int $excludePreProjectId = null): bool
    {
        // Residen users are not subject to budget validation
        if (!$this->isSubjectToBudgetValidation($user)) {
            return false;
        }
        
        $budgetData = $this->getUserBudgetData($user);
        
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
     * @return float
     */
    public function getRemainingBudgetAfter(User $user, float $projectCost, ?int $excludePreProjectId = null): float
    {
        $budgetData = $this->getUserBudgetData($user);
        
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
}
