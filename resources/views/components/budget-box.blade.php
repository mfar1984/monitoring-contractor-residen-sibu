@props(['year' => null])

@php
    // Default to current year if not provided
    $year = $year ?? now()->year;
    
    // Get budget information from BudgetCalculationService
    $budgetInfo = app(\App\Services\BudgetCalculationService::class)
        ->getUserBudgetInfo(Auth::user(), $year);
    
    // Handle null case (for non-Parliament/DUN users)
    if (!$budgetInfo) {
        $totalBudget = 0;
        $allocatedBudget = 0;
        $remainingBudget = 0;
        $sourceName = '';
    } else {
        $totalBudget = $budgetInfo['total_budget'] ?? 0;
        $allocatedBudget = $budgetInfo['total_allocated'] ?? 0;
        $remainingBudget = $budgetInfo['remaining_budget'] ?? 0;
        $sourceName = $budgetInfo['source_name'] ?? '';
    }
@endphp

<div style="margin-bottom: 20px;">
    <!-- Year Header -->
    <div style="margin-bottom: 10px; font-size: 14px; font-weight: 600; color: #333;">
        Budget for Year {{ $year }}
    </div>
    
    <!-- No Budget Warning -->
    @if($totalBudget == 0)
        <div style="padding: 15px; text-align: center; color: #856404; background-color: #fff3cd; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeaa7;">
            <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle;">warning</span>
            <strong>No budget allocated for year {{ $year }}.</strong>
            <br>
            <small>Please contact administrator to set up budget allocation for this year.</small>
        </div>
    @endif
    
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
        <!-- Total Budget Box -->
        <div class="budget-item total-budget">
            <div class="budget-label">Total Budget</div>
            <div class="budget-value">RM {{ number_format($totalBudget, 2) }}</div>
            <div class="budget-source">{{ $sourceName }}</div>
        </div>
        
        <!-- Total Allocated Box -->
        <div class="budget-item total-allocated">
            <div class="budget-label">Total Allocated</div>
            <div class="budget-value">RM {{ number_format($allocatedBudget, 2) }}</div>
        </div>
        
        <!-- Remaining Budget Box -->
        <div class="budget-item remaining {{ $remainingBudget < 0 ? 'budget-exceeded' : '' }}">
            <div class="budget-label">Remaining Budget</div>
            <div class="budget-value" style="color: {{ $remainingBudget < 0 ? '#dc3545' : '#28a745' }};">
                RM {{ number_format($remainingBudget, 2) }}
            </div>
        </div>
    </div>
</div>
