<?php

namespace App\Services;

use App\Models\User;
use App\Models\Parliament;
use App\Models\Dun;
use App\Models\PreProject;
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
     * for the user's Parliament or DUN constituency.
     * 
     * @param User $user The authenticated user
     * @return array [
     *   'total_budget' => float,
     *   'allocated_budget' => float,
     *   'remaining_budget' => float,
     *   'source_type' => string ('parliament' or 'dun'),
     *   'source_name' => string,
     *   'source_id' => int
     * ]
     */
    public function getUserBudgetInfo(User $user): array
    {
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
                    $totalBudget = (float) ($parliament->budget ?? 0);
                    $allocatedBudget = $this->calculateAllocatedBudget('parliament', $user->parliament_id);
                    $sourceType = 'parliament';
                    $sourceName = $parliament->name;
                    $sourceId = $parliament->id;
                }
            }
            // Check if user has DUN
            elseif ($user->dun_id) {
                $dun = Dun::find($user->dun_id);
                if ($dun) {
                    $totalBudget = (float) ($dun->budget ?? 0);
                    $allocatedBudget = $this->calculateAllocatedBudget('dun', $user->dun_id);
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
            ];
        } catch (\Exception $e) {
            Log::error('Budget calculation failed for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'total_budget' => 0,
                'allocated_budget' => 0,
                'remaining_budget' => 0,
                'source_type' => null,
                'source_name' => '',
                'source_id' => null,
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
     * @return float Total allocated budget
     */
    public function calculateAllocatedBudget(string $type, int $id): float
    {
        try {
            $query = PreProject::query();

            if ($type === 'parliament') {
                $query->where('parliament_id', $id);
            } elseif ($type === 'dun') {
                $query->where('dun_id', $id);
            } else {
                return 0;
            }

            // Exclude cancelled and rejected projects
            $query->whereNotIn('status', ['Cancelled', 'Rejected']);

            $total = $query->sum('total_cost');

            return (float) ($total ?? 0);
        } catch (\Exception $e) {
            Log::error('Allocated budget calculation failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Check if a cost amount is within remaining budget
     * 
     * Validates whether a proposed cost fits within the available budget.
     * For edit operations, excludes the original project cost from calculation.
     * 
     * @param User $user The authenticated user
     * @param float $cost The proposed cost
     * @param int|null $excludePreProjectId Pre-project ID to exclude (for edit operations)
     * @return bool True if within budget, false otherwise
     */
    public function isWithinBudget(User $user, float $cost, ?int $excludePreProjectId = null): bool
    {
        try {
            $budgetInfo = $this->getUserBudgetInfo($user);
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
                'exclude_id' => $excludePreProjectId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get available budget for editing a pre-project
     * 
     * Calculates how much budget is available for a specific pre-project edit.
     * This includes the current remaining budget plus the original project cost.
     * 
     * @param User $user The authenticated user
     * @param PreProject $preProject The pre-project being edited
     * @return float Available budget for this project
     */
    public function getAvailableBudgetForEdit(User $user, PreProject $preProject): float
    {
        try {
            $budgetInfo = $this->getUserBudgetInfo($user);
            $remainingBudget = $budgetInfo['remaining_budget'];
            $originalCost = (float) $preProject->total_cost;

            return $remainingBudget + $originalCost;
        } catch (\Exception $e) {
            Log::error('Available budget calculation failed for edit', [
                'user_id' => $user->id,
                'pre_project_id' => $preProject->id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }
}
