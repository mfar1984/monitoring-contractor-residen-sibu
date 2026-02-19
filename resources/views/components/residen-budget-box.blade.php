@props(['year' => null])

@php
    // Default to current year if not provided
    $year = $year ?? now()->year;
    
    // Get Residen budget information from BudgetCalculationService
    $residenBudgetInfo = app(\App\Services\BudgetCalculationService::class)
        ->getResidenBudgetInfo(Auth::user(), $year);
    
    $totalBudgetParliament = $residenBudgetInfo['total_budget_parliament'];
    $totalBudgetDun = $residenBudgetInfo['total_budget_dun'];
    $totalAllocatedParliament = $residenBudgetInfo['total_allocated_parliament'];
    $totalAllocatedDun = $residenBudgetInfo['total_allocated_dun'];
    $remainingBudget = $residenBudgetInfo['remaining_budget'];
@endphp

<div style="margin-bottom: 20px;">
    <!-- Year Header -->
    <div style="margin-bottom: 10px; font-size: 14px; font-weight: 600; color: #333;">
        Residen Budget Overview for Year {{ $year }}
    </div>
    
    <!-- Single row with 5 boxes -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px;">
        <!-- Box 1: Total Budget Parliament -->
        <div class="budget-item total-budget">
            <div class="budget-label">Total Budget Parliament</div>
            <div class="budget-value">RM {{ number_format($totalBudgetParliament, 2) }}</div>
        </div>
        
        <!-- Box 2: Total Budget DUN -->
        <div class="budget-item total-budget">
            <div class="budget-label">Total Budget DUN</div>
            <div class="budget-value">RM {{ number_format($totalBudgetDun, 2) }}</div>
        </div>
        
        <!-- Box 3: Total Allocated Parliament -->
        <div class="budget-item total-allocated">
            <div class="budget-label">Total Allocated Parliament</div>
            <div class="budget-value">RM {{ number_format($totalAllocatedParliament, 2) }}</div>
        </div>
        
        <!-- Box 4: Total Allocated DUN -->
        <div class="budget-item total-allocated">
            <div class="budget-label">Total Allocated DUN</div>
            <div class="budget-value">RM {{ number_format($totalAllocatedDun, 2) }}</div>
        </div>
        
        <!-- Box 5: Remaining Budget -->
        <div class="budget-item remaining {{ $remainingBudget < 0 ? 'budget-exceeded' : '' }}">
            <div class="budget-label">Remaining Budget</div>
            <div class="budget-value" style="color: {{ $remainingBudget < 0 ? '#dc3545' : '#28a745' }};">
                RM {{ number_format($remainingBudget, 2) }}
            </div>
        </div>
    </div>
</div>
