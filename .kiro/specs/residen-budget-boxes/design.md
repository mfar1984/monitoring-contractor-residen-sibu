# Design Document: Residen Budget Boxes

## Overview

This feature extends the pre-project page to display aggregated budget information for Residen users. The implementation adds five new budget boxes that show consolidated budget data across all Parliament and DUN constituencies. The design leverages the existing budget-box component and BudgetCalculationService, adding new methods for aggregated calculations while maintaining visual consistency with the current interface.

The feature is role-based: only users with a `residen_category_id` will see these additional boxes. The existing budget boxes for Parliament and DUN users remain unchanged.

## Architecture

### Component Structure

```
┌─────────────────────────────────────────────────────────────┐
│                    Pre-Project Page                          │
│                                                              │
│  FOR PARLIAMENT/DUN USERS (Existing - No Changes):          │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Budget Box (1 Row with 3 Boxes)                      │  │
│  │  ┌─────────────┬─────────────┬─────────────────────┐  │  │
│  │  │ Total Budget│ Total       │ Remaining Budget    │  │  │
│  │  │             │ Allocated   │                     │  │  │
│  │  └─────────────┴─────────────┴─────────────────────┘  │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  FOR RESIDEN USERS (New - Conditional Display):             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Residen Budget Boxes (1 Row with 5 Boxes)            │  │
│  │  ┌──────┬──────┬──────┬──────┬──────────────────┐     │  │
│  │  │Total │Total │Total │Total │Remaining Budget  │     │  │
│  │  │Budget│Budget│Alloc │Alloc │(Parliament + DUN)│     │  │
│  │  │Parl. │DUN   │Parl. │DUN   │                  │     │  │
│  │  └──────┴──────┴──────┴──────┴──────────────────┘     │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Pre-Project Data Table                               │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

```
User Request
    ↓
PageController::preProject()
    ↓
Check if user has residen_category_id
    ↓
If Residen user:
    ↓
BudgetCalculationService::getResidenBudgetInfo($user, $year)
    ↓
    ├─→ Calculate Total Budget Parliament (SUM parliament_budgets.budget)
    ├─→ Calculate Total Budget DUN (SUM dun_budgets.budget)
    ├─→ Calculate Total Allocated Parliament (SUM pre_projects.total_cost WHERE parliament_id)
    ├─→ Calculate Total Allocated DUN (SUM pre_projects.total_cost WHERE dun_id)
    └─→ Calculate Remaining Budget (Total Budgets - Total Allocated)
    ↓
Pass $residenBudgetInfo to view
    ↓
Blade Template renders Residen budget boxes
```

## Components and Interfaces

### 1. BudgetCalculationService Extension

**Location**: `app/Services/BudgetCalculationService.php`

**New Method**: `getResidenBudgetInfo(User $user, ?int $year = null): array`

```php
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
    $year = $year ?? now()->year;
    
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
        'total_budget_parliament' => (float) $totalBudgetParliament,
        'total_budget_dun' => (float) $totalBudgetDun,
        'total_allocated_parliament' => (float) $totalAllocatedParliament,
        'total_allocated_dun' => (float) $totalAllocatedDun,
        'remaining_budget' => $remainingBudget,
        'year' => $year,
    ];
}
```

### 2. PageController Modification

**Location**: `app/Http/Controllers/Pages/PageController.php`

**Method**: `preProject()`

**Changes**:
```php
public function preProject(): View
{
    $user = auth()->user();
    
    // ... existing code ...
    
    // Get budget information for the user
    $budgetService = new \App\Services\BudgetCalculationService();
    $budgetInfo = $budgetService->getUserBudgetInfo($user);
    
    // NEW: Get Residen budget info if user is Residen
    $residenBudgetInfo = null;
    if ($user->residen_category_id) {
        $residenBudgetInfo = $budgetService->getResidenBudgetInfo($user);
    }
    
    // ... existing code ...
    
    return view('pages.pre-project', compact(
        'user',
        'preProjects',
        'budgetInfo',
        'residenBudgetInfo', // NEW
        'residenCategories',
        // ... other variables ...
    ));
}
```

### 3. Blade Component: Residen Budget Box

**Location**: `resources/views/components/residen-budget-box.blade.php`

**Purpose**: Reusable component for displaying Residen aggregated budget information

**Props**:
- `year` (optional): The fiscal year to display (defaults to current year)

**Implementation**:
```blade
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
```

### 4. Pre-Project Page Modification

**Location**: `resources/views/pages/pre-project.blade.php`

**Changes**: Add conditional rendering for Residen budget boxes

```blade
{{-- Existing Budget Box Component - Shows for Parliament/DUN users (3 boxes in 1 row) --}}
@if(isset($budgetInfo) && $budgetInfo['source_name'])
    <x-budget-box :year="$budgetInfo['year'] ?? now()->year" />
@endif

{{-- NEW: Residen Budget Boxes - Only for Residen users (5 boxes in 1 row) --}}
@if($user->residen_category_id && isset($residenBudgetInfo))
    <x-residen-budget-box :year="$residenBudgetInfo['year'] ?? now()->year" />
@endif
```

**Key Points**:
- Parliament/DUN users see existing 3-box layout (no changes)
- Residen users see new 5-box layout in a single row
- Both layouts use the same CSS classes for consistency
- Conditional rendering ensures proper display based on user role

## Data Models

### Existing Models (No Changes Required)

**Parliament Model** (`app/Models/Parliament.php`):
- Already has `budgets()` relationship to `ParliamentBudget`
- Already has `getBudgetForYear($year)` method

**Dun Model** (`app/Models/Dun.php`):
- Already has `budgets()` relationship to `DunBudget`
- Already has `getBudgetForYear($year)` method

**PreProject Model** (`app/Models/PreProject.php`):
- Already has `parliament_id` and `dun_id` foreign keys
- Already has `project_year` field
- Already has `total_cost` field
- Already has `status` field

### Database Tables (No Changes Required)

**parliament_budgets**:
- `id` (primary key)
- `parliament_id` (foreign key)
- `year` (year)
- `budget` (decimal 15,2)
- Indexed on `(parliament_id, year)`

**dun_budgets**:
- `id` (primary key)
- `dun_id` (foreign key)
- `year` (year)
- `budget` (decimal 15,2)
- Indexed on `(dun_id, year)`

**pre_projects**:
- `parliament_id` (nullable foreign key)
- `dun_id` (nullable foreign key)
- `project_year` (year)
- `total_cost` (decimal 15,2)
- `status` (string)

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Budget Calculation Accuracy

*For any* fiscal year, the sum of Total Budget Parliament and Total Budget DUN should equal the sum of all parliament_budgets.budget and dun_budgets.budget values for that year.

**Validates: Requirements 2.1, 3.1**

### Property 2: Allocated Budget Exclusion

*For any* fiscal year, when calculating Total Allocated Parliament or Total Allocated DUN, pre-projects with status "Cancelled" or "Rejected" should not be included in the sum.

**Validates: Requirements 4.2, 5.2**

### Property 3: Remaining Budget Calculation

*For any* fiscal year, the Remaining Budget should equal (Total Budget Parliament + Total Budget DUN) - (Total Allocated Parliament + Total Allocated DUN).

**Validates: Requirements 6.1**

### Property 4: Conditional Display

*For any* user without residen_category_id, the Residen budget boxes should not be rendered in the DOM.

**Validates: Requirements 1.2**

### Property 5: Year Consistency

*For any* budget calculation request, all budget values (Parliament, DUN, Allocated) should be calculated for the same fiscal year.

**Validates: Requirements 7.1, 7.4**

### Property 6: Non-Negative Budget Values

*For any* fiscal year, Total Budget Parliament, Total Budget DUN, Total Allocated Parliament, and Total Allocated DUN should never be negative values.

**Validates: Requirements 2.2, 3.2, 4.3, 5.3**

### Property 7: Visual Consistency

*For any* budget box rendered, the CSS classes and styling should match the existing budget-box component styling.

**Validates: Requirements 1.4, 8.2**

### Property 8: Null Handling

*For any* budget calculation where no budget records exist for a year, the system should return 0.00 instead of null or throwing an error.

**Validates: Requirements 2.2, 3.2, 4.3, 5.3, 9.4**

## Error Handling

### Database Query Errors

**Scenario**: Database connection fails or query times out

**Handling**:
```php
try {
    $totalBudgetParliament = DB::table('parliament_budgets')
        ->where('year', $year)
        ->sum('budget');
} catch (\Exception $e) {
    Log::error('Failed to calculate Parliament budget', [
        'year' => $year,
        'error' => $e->getMessage()
    ]);
    $totalBudgetParliament = 0;
}
```

**User Experience**: Display RM 0.00 with a subtle warning message

### Missing Year Parameter

**Scenario**: Year parameter is null or invalid

**Handling**: Default to current year using `now()->year`

**User Experience**: No error shown, uses current year automatically

### User Without Residen Role

**Scenario**: Non-Residen user attempts to access Residen budget info

**Handling**: Conditional check in controller and view prevents rendering

**User Experience**: Residen budget boxes simply don't appear

### Null Budget Values

**Scenario**: No budget records exist for a specific year

**Handling**: Use `(float)` casting and null coalescing to ensure 0 is returned

**User Experience**: Display RM 0.00 for all budget values

### Negative Remaining Budget

**Scenario**: Allocated budget exceeds total budget

**Handling**: Display negative value in red color

**User Experience**: Clear visual indicator of over-budget situation

## Testing Strategy

### Unit Tests

**Test File**: `tests/Unit/BudgetCalculationServiceTest.php`

**Test Cases**:

1. **test_residen_budget_info_returns_correct_structure**
   - Verify return array has all required keys
   - Verify all values are floats
   - Verify year is an integer

2. **test_residen_budget_calculates_parliament_total_correctly**
   - Create 3 Parliament budgets for year 2024
   - Verify sum matches expected total

3. **test_residen_budget_calculates_dun_total_correctly**
   - Create 5 DUN budgets for year 2024
   - Verify sum matches expected total

4. **test_residen_budget_excludes_cancelled_projects**
   - Create pre-projects with status "Cancelled"
   - Verify they are not included in allocated totals

5. **test_residen_budget_excludes_rejected_projects**
   - Create pre-projects with status "Rejected"
   - Verify they are not included in allocated totals

6. **test_residen_budget_handles_no_budgets_gracefully**
   - Query for year with no budget records
   - Verify returns 0.00 for all values

7. **test_residen_budget_filters_by_year_correctly**
   - Create budgets for years 2023, 2024, 2025
   - Query for 2024
   - Verify only 2024 budgets are included

8. **test_remaining_budget_calculation_is_accurate**
   - Create known budget and allocation values
   - Verify remaining = (parliament + dun) - (allocated_parliament + allocated_dun)

### Property-Based Tests

**Test File**: `tests/Feature/ResidenBudgetPropertiesTest.php`

**Property Test Configuration**: Minimum 100 iterations per test

**Property Test 1: Budget Sum Accuracy**
```php
/**
 * Feature: residen-budget-boxes, Property 1: Budget Calculation Accuracy
 * For any fiscal year, the sum of Total Budget Parliament and Total Budget DUN 
 * should equal the sum of all parliament_budgets.budget and dun_budgets.budget 
 * values for that year.
 */
public function test_property_budget_sum_accuracy()
{
    // Generate random year between 2024-2030
    // Generate random number of Parliament budgets (1-10)
    // Generate random number of DUN budgets (1-20)
    // Generate random budget amounts (1000-1000000)
    
    // Calculate expected sum manually
    // Call getResidenBudgetInfo()
    // Assert total_budget_parliament + total_budget_dun equals expected sum
}
```

**Property Test 2: Cancelled Projects Exclusion**
```php
/**
 * Feature: residen-budget-boxes, Property 2: Allocated Budget Exclusion
 * For any fiscal year, when calculating Total Allocated Parliament or Total 
 * Allocated DUN, pre-projects with status "Cancelled" or "Rejected" should 
 * not be included in the sum.
 */
public function test_property_cancelled_projects_excluded()
{
    // Generate random year
    // Generate random pre-projects with various statuses
    // Include some with "Cancelled" and "Rejected" status
    
    // Calculate expected allocated (excluding Cancelled/Rejected)
    // Call getResidenBudgetInfo()
    // Assert allocated totals match expected (without Cancelled/Rejected)
}
```

**Property Test 3: Remaining Budget Formula**
```php
/**
 * Feature: residen-budget-boxes, Property 3: Remaining Budget Calculation
 * For any fiscal year, the Remaining Budget should equal 
 * (Total Budget Parliament + Total Budget DUN) - 
 * (Total Allocated Parliament + Total Allocated DUN).
 */
public function test_property_remaining_budget_formula()
{
    // Generate random year
    // Generate random budgets and allocations
    
    // Call getResidenBudgetInfo()
    // Assert remaining_budget = 
    //   (total_budget_parliament + total_budget_dun) - 
    //   (total_allocated_parliament + total_allocated_dun)
}
```

**Property Test 4: Year Filtering Consistency**
```php
/**
 * Feature: residen-budget-boxes, Property 5: Year Consistency
 * For any budget calculation request, all budget values (Parliament, DUN, 
 * Allocated) should be calculated for the same fiscal year.
 */
public function test_property_year_filtering_consistency()
{
    // Generate budgets for multiple years (2023, 2024, 2025)
    // Generate pre-projects for multiple years
    
    // For each year, call getResidenBudgetInfo()
    // Verify only that year's data is included
    // Verify no cross-year contamination
}
```

**Property Test 5: Non-Negative Values**
```php
/**
 * Feature: residen-budget-boxes, Property 6: Non-Negative Budget Values
 * For any fiscal year, Total Budget Parliament, Total Budget DUN, 
 * Total Allocated Parliament, and Total Allocated DUN should never be 
 * negative values.
 */
public function test_property_non_negative_values()
{
    // Generate random budgets (always positive)
    // Generate random allocations (always positive)
    
    // Call getResidenBudgetInfo()
    // Assert all budget values >= 0
    // Note: remaining_budget CAN be negative (over-budget scenario)
}
```

**Property Test 6: Null Handling**
```php
/**
 * Feature: residen-budget-boxes, Property 8: Null Handling
 * For any budget calculation where no budget records exist for a year, 
 * the system should return 0.00 instead of null or throwing an error.
 */
public function test_property_null_handling()
{
    // Generate random year with NO budget records
    
    // Call getResidenBudgetInfo()
    // Assert all values are 0.00 (not null)
    // Assert no exception is thrown
}
```

### Integration Tests

**Test File**: `tests/Feature/ResidenBudgetDisplayTest.php`

**Test Cases**:

1. **test_residen_user_sees_budget_boxes**
   - Create user with residen_category_id
   - Visit pre-project page
   - Assert Residen budget boxes are visible

2. **test_non_residen_user_does_not_see_budget_boxes**
   - Create user without residen_category_id
   - Visit pre-project page
   - Assert Residen budget boxes are NOT visible

3. **test_budget_boxes_display_correct_values**
   - Create known budget data
   - Visit pre-project page as Residen user
   - Assert displayed values match expected calculations

4. **test_negative_remaining_budget_displays_in_red**
   - Create over-budget scenario
   - Visit pre-project page
   - Assert remaining budget has red color styling

5. **test_year_parameter_is_respected**
   - Create budgets for year 2025
   - Visit pre-project page with year=2025
   - Assert displayed year is 2025
   - Assert values are for 2025

### Browser Tests (Optional)

**Test File**: `tests/Browser/ResidenBudgetBoxTest.php`

**Test Cases**:

1. **test_budget_boxes_render_with_correct_styling**
   - Verify CSS classes match existing budget-box component
   - Verify grid layout displays correctly
   - Verify responsive behavior

2. **test_budget_values_format_correctly**
   - Verify currency formatting (RM prefix, 2 decimals, thousand separators)
   - Verify color coding for negative values

## Performance Considerations

### Database Query Optimization

**Current Approach**: Four separate SUM queries
- Query 1: SUM parliament_budgets.budget
- Query 2: SUM dun_budgets.budget
- Query 3: SUM pre_projects.total_cost (Parliament)
- Query 4: SUM pre_projects.total_cost (DUN)

**Optimization**: All queries use indexed columns (`year`, `parliament_id`, `dun_id`)

**Expected Performance**: < 50ms for typical dataset (100 Parliaments, 200 DUNs, 1000 pre-projects)

### Caching Strategy (Future Enhancement)

**Cache Key**: `residen_budget_{year}`

**Cache Duration**: 1 hour

**Cache Invalidation**: When budget or pre-project is created/updated/deleted

**Implementation** (not in initial release):
```php
public function getResidenBudgetInfo(User $user, ?int $year = null): array
{
    $year = $year ?? now()->year;
    $cacheKey = "residen_budget_{$year}";
    
    return Cache::remember($cacheKey, 3600, function () use ($year) {
        // Existing calculation logic
    });
}
```

### N+1 Query Prevention

**Not Applicable**: This feature uses aggregate queries (SUM), not iterative model loading

### Memory Usage

**Estimated Memory**: < 1MB per request (only aggregate values, no model collections)

## Security Considerations

### Authorization

**Role Check**: Only users with `residen_category_id` can see Residen budget boxes

**Implementation**: Conditional rendering in Blade template

**No Additional Permissions Required**: Residen users already have access to pre-project page

### Data Exposure

**Concern**: Residen users see aggregated budget data across all constituencies

**Mitigation**: This is intentional - Residen role is administrative and requires oversight

**No Sensitive Data**: Budget amounts are not considered sensitive in this context

### SQL Injection

**Protection**: Using Laravel Query Builder with parameter binding

**No Raw Queries**: All queries use `DB::table()` with `where()` and `sum()` methods

### XSS Prevention

**Protection**: Blade `{{ }}` syntax auto-escapes output

**Currency Formatting**: Using `number_format()` which returns safe strings

## Deployment Considerations

### Migration Requirements

**None**: No database schema changes required

### Seeder Requirements

**None**: Feature uses existing budget data

### Configuration Changes

**None**: No new configuration values needed

### Asset Compilation

**None**: Uses existing CSS from budget-box component

### Rollback Plan

If issues arise:
1. Remove `getResidenBudgetInfo()` method from BudgetCalculationService
2. Remove Residen budget box component file
3. Remove conditional rendering from pre-project page
4. Clear view cache: `php artisan view:clear`

### Monitoring

**Metrics to Track**:
- Page load time for pre-project page (should not increase significantly)
- Database query execution time for budget calculations
- Error rate for budget calculation failures

**Logging**:
- Log any exceptions in `getResidenBudgetInfo()` method
- Log when budget calculations return unexpected values (e.g., extremely large numbers)

## Future Enhancements

### 1. Budget Drill-Down

Allow Residen users to click on budget boxes to see detailed breakdown by Parliament/DUN

### 2. Year Comparison

Add ability to compare budget data across multiple years side-by-side

### 3. Export Functionality

Add button to export Residen budget summary to PDF or Excel

### 4. Budget Alerts

Send notifications when budget utilization reaches certain thresholds (e.g., 80%, 90%, 100%)

### 5. Historical Trends

Display charts showing budget trends over multiple years

### 6. Budget Forecasting

Predict future budget needs based on historical allocation patterns
