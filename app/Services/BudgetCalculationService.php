<?php

namespace App\Services;

use App\Models\User;
use App\Models\Parliament;
use App\Models\Dun;
use App\Models\PreProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Budget Calculation Service
 * 
 * Handles all budget-related calculations for Parliament and DUN constituencies.
 * Calculates total budget, allocated budget, and remaining budget for pre-projects.
 */
class BudgetCalculationService
{
    /**
     * Get budget information for a user
     * 
     * Retrieves the total budget, allocated budget, and remaining budget
     * for the user's Parliament or DUN constituency for a specific year.
     * 
     * @param User $user The authenticated user
     * @param int|null $year The fiscal year (defaults to current year)
     * @return array [
     *   'total_budget' => float,
     *   'allocated_budget' => float,
     *   'remaining_budget' => float,
     *   'source_type' => string ('parliament' or 'dun'),
     *   'source_name' => string,
     *   'source_id' => int,
     *   'year' => int
     * ]
     */
    public function getUserBudgetInfo(User $user, ?int $year = null): array
    {
        // Default to current year if not specified
        $year = $year ?? now()->year;
        
        $totalBudget = 0;
        $allocatedBudget = 0;
        $sourceType = null;
        $sourceName = '';
        $sourceId = null;

        try {
            // Check if user has Parliament
            if ($user->parliament_id) {
                $parliament = Parliament::find($user->parliament_id);
                if ($parliament) {
                    $totalBudget = (float) $parliament->getBudgetForYear($year);
                    $allocatedBudget = $this->calculateAllocatedBudget('parliament', $user->parliament_id, $year);
                    $sourceType = 'parliament';
                    $sourceName = $parliament->name;
                    $sourceId = $parliament->id;
                }
            }
            // Check if user has DUN
            elseif ($user->dun_id) {
                $dun = Dun::find($user->dun_id);
                if ($dun) {
                    $totalBudget = (float) $dun->getBudgetForYear($year);
                    $allocatedBudget = $this->calculateAllocatedBudget('dun', $user->dun_id, $year);
                    $sourceType = 'dun';
                    $sourceName = $dun->name;
                    $sourceId = $dun->id;
                }
            }

            $remainingBudget = $totalBudget - $allocatedBudget;

            return [
                'total_budget' => $totalBudget,
                'allocated_budget' => $allocatedBudget,
                'remaining_budget' => $remainingBudget,
                'source_type' => $sourceType,
                'source_name' => $sourceName,
                'source_id' => $sourceId,
                'year' => $year,
            ];
        } catch (\Exception $e) {
            Log::error('Budget calculation failed for user', [
                'user_id' => $user->id,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return [
                'total_budget' => 0,
                'allocated_budget' => 0,
                'remaining_budget' => 0,
                'source_type' => null,
                'source_name' => '',
                'source_id' => null,
                'year' => $year,
            ];
        }
    }

    /**
     * Calculate total allocated budget for Parliament or DUN
     * 
     * Sums all pre-project costs for a specific Parliament or DUN,
     * excluding projects with status "Cancelled" or "Rejected".
     * 
     * @param string $type 'parliament' or 'dun'
     * @param int $id Parliament or DUN ID
     * @param int|null $year The fiscal year to filter by (defaults to current year)
     * @return float Total allocated budget
     */
    public function calculateAllocatedBudget(string $type, int $id, ?int $year = null): float
    {
        try {
            // Default to current year if not specified
            $year = $year ?? now()->year;
            
            $query = PreProject::query();

            if ($type === 'parliament') {
                $query->where('parliament_id', $id);
            } elseif ($type === 'dun') {
                $query->where('dun_id', $id);
            } else {
                return 0;
            }

            // Filter by project year
            $query->where('project_year', $year);

            // Exclude cancelled and rejected projects
            $query->whereNotIn('status', ['Cancelled', 'Rejected']);

            $total = $query->sum('total_cost');

            return (float) ($total ?? 0);
        } catch (\Exception $e) {
            Log::error('Allocated budget calculation failed', [
                'type' => $type,
                'id' => $id,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Check if a cost amount is within remaining budget
     * 
     * Validates whether a proposed cost fits within the available budget for a specific year.
     * For edit operations, excludes the original project cost from calculation.
     * 
     * @param User $user The authenticated user
     * @param float $cost The proposed cost
     * @param int $year The fiscal year to validate against
     * @param int|null $excludePreProjectId Pre-project ID to exclude (for edit operations)
     * @return bool True if within budget, false otherwise
     */
    public function isWithinBudget(User $user, float $cost, int $year, ?int $excludePreProjectId = null): bool
    {
        try {
            // Get budget info for the specified year
            $budgetInfo = $this->getUserBudgetInfo($user, $year);
            $availableBudget = $budgetInfo['remaining_budget'];

            // For edit operations, add back the original project cost
            if ($excludePreProjectId) {
                $preProject = PreProject::find($excludePreProjectId);
                if ($preProject) {
                    $availableBudget += (float) $preProject->total_cost;
                }
            }

            return $cost <= $availableBudget;
        } catch (\Exception $e) {
            Log::error('Budget validation failed', [
                'user_id' => $user->id,
                'cost' => $cost,
                'year' => $year,
                'exclude_id' => $excludePreProjectId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get available budget for editing a pre-project
     * 
     * Calculates how much budget is available for a specific pre-project edit for a given year.
     * This includes the current remaining budget plus the original project cost.
     * 
     * @param User $user The authenticated user
     * @param PreProject $preProject The pre-project being edited
     * @param int $year The fiscal year to calculate for
     * @return float Available budget for this project
     */
    public function getAvailableBudgetForEdit(User $user, PreProject $preProject, int $year): float
    {
        try {
            // Get budget info for the specified year
            $budgetInfo = $this->getUserBudgetInfo($user, $year);
            $remainingBudget = $budgetInfo['remaining_budget'];
            $originalCost = (float) $preProject->total_cost;

            return $remainingBudget + $originalCost;
        } catch (\Exception $e) {
            Log::error('Available budget calculation failed for edit', [
                'user_id' => $user->id,
                'pre_project_id' => $preProject->id,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
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
            $totalAllocatedParliament = DB::table('pre_projects')
                ->whereNotNull('parliament_id')
                ->where('project_year', $year)
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
                ->sum('total_cost');
            
            // Calculate total allocated to DUN pre-projects
            $totalAllocatedDun = DB::table('pre_projects')
                ->whereNotNull('dun_id')
                ->where('project_year', $year)
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
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
