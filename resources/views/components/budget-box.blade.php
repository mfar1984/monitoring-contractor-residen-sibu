@props(['totalBudget', 'allocatedBudget', 'remainingBudget', 'sourceName'])

<div style="margin-bottom: 20px;">
    @if($totalBudget == 0)
        <div style="padding: 15px; text-align: center; color: #856404; background-color: #fff3cd; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeaa7;">
            <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle;">warning</span>
            <strong>No budget allocated for your constituency.</strong>
            <br>
            <small>Please contact administrator to set up budget allocation.</small>
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
