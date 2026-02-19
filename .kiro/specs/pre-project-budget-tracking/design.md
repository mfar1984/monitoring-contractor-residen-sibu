# Design Document: Pre-Project Budget Tracking

## Overview

The Pre-Project Budget Tracking system provides real-time budget monitoring and validation for Parliament and DUN users. The system displays budget information in three key locations: (1) budget boxes on the pre-project list page, (2) budget reminders in create/edit modals, and (3) aggregated budget overview for Residen users. The design emphasizes real-time calculation, visual feedback, and budget constraint enforcement to prevent overspending.

The system integrates with existing Laravel models (Parliament, Dun, PreProject, User) and leverages the current authentication system to determine user roles and budget scope. Budget calculations are performed server-side for accuracy, with client-side JavaScript providing real-time feedback during data entry.

## Architecture

### System Components

1. **BudgetCalculationService**: Centralized service for all budget calculations
2. **BudgetBoxComponent**: Reusable Blade component for displaying budget information
3. **BudgetValidationMiddleware**: Validates budget constraints before saving pre-projects
4. **Client-Side Budget Calculator**: JavaScript for real-time budget updates in modals
5. **Budget Display Views**: Blade templates for budget boxes on various pages

### Data Flow

```
User Request → Controller → BudgetCalculationService → Database Query
                ↓
         Budget Data → View → Budget Box Component → Rendered HTML
                ↓
         JavaScript → Real-time Updates → DOM Manipulation
```

### Integration Points

- **User Authentication**: Uses Auth::user() to determine user role and scope
- **Parliament/DUN Models**: Retrieves budget allocation data
- **PreProject Model**: Queries total_cost for budget calculations
- **Existing Controllers**: PageController methods extended with budget data
- **Blade Components**: New budget-box component integrated into existing views

## Components and Interfaces

### 1. BudgetCalculationService

**Purpose**: Centralized service for all budget-related calculations

**Methods**:

```php
class BudgetCalculationService
{
    /**
     * Get budget data for a Parliament/DUN user
     * @param User $user
     * @param int|null $year (defaults to current year)
     * @return array ['total_budget', 'total_allocated', 'remaining_budget', 'year']
     */
    public function getUserBudgetData(User $user, ?int $year = null): array

    /**
     * Get aggregated budget data for Residen users
     * @param int|null $year (defaults to current year)
     * @return array ['total_parliament_budget', 'total_dun_budget', 'total_allocated', 'overall_remaining', 'year']
     */
    public function getResidenBudgetOverview(?int $year = null): array

    /**
     * Calculate if a pre-project would exceed budget
     * @param User $user
     * @param float $projectCost
     * @param int|null $excludePreProjectId (for edit scenarios)
     * @return bool
     */
    public function wouldExceedBudget(User $user, float $projectCost, ?int $excludePreProjectId = null): bool

    /**
     * Get remaining budget after hypothetical pre-project
     * @param User $user
     * @param float $projectCost
     * @param int|null $excludePreProjectId
     * @return float
     */
    public function getRemainingBudgetAfter(User $user, float $projectCost, ?int $excludePreProjectId = null): float

    /**
     * Check if user is subject to budget validation
     * @param User $user
     * @return bool
     */
    public function isSubjectToBudgetValidation(User $user): bool
}
```

**Implementation Notes**:
- Uses Eloquent queries with proper eager loading
- Caches budget data within request lifecycle to avoid duplicate queries
- Handles null budget values gracefully (treats as 0)
- Supports multi-year budget queries (future-proof)

### 2. Budget Box Blade Component

**Component**: `resources/views/components/budget-box.blade.php`

**Props**:
```php
@props([
    'title',           // string: Box title (e.g., "TOTAL BUDGET")
    'amount',          // float: Amount to display
    'color',           // string: 'blue', 'green', 'yellow', 'red'
    'year',            // int: Budget year
    'isNegative' => false  // bool: Whether to highlight as negative
])
```

**Usage Example**:
```blade
<x-budget-box 
    title="TOTAL BUDGET" 
    :amount="$budgetData['total_budget']" 
    color="blue" 
    :year="$budgetData['year']" 
/>

<x-budget-box 
    title="REMAINING BUDGET" 
    :amount="$budgetData['remaining_budget']" 
    :color="$budgetData['remaining_budget'] < 0 ? 'red' : 'yellow'" 
    :year="$budgetData['year']"
    :isNegative="$budgetData['remaining_budget'] < 0"
/>
```

**Styling**:
- Gradient backgrounds based on color prop
- Large, bold typography for amounts
- Currency formatting: RM X,XXX,XXX.XX
- Responsive grid layout (3 boxes per row on desktop, stacked on mobile)
- Red text and border when isNegative is true

### 3. Budget Reminder Component (Modal)

**Component**: `resources/views/components/budget-reminder.blade.php`

**Props**:
```php
@props([
    'remainingBudget',  // float: Current remaining budget
    'projectCost' => 0  // float: Current project cost being entered
])
```

**Features**:
- Compact, inline display below cost input fields
- Real-time JavaScript updates as user types
- Color changes: green (within budget) → red (exceeds budget)
- Shows calculated remaining after current entry

**JavaScript Integration**:
```javascript
// budget-calculator.js
function updateBudgetReminder(remainingBudget, projectCost) {
    const newRemaining = remainingBudget - projectCost;
    const reminderBox = document.getElementById('budget-reminder');
    const saveButton = document.getElementById('save-button');
    
    reminderBox.textContent = `Remaining Budget: RM ${formatCurrency(newRemaining)}`;
    
    if (newRemaining < 0) {
        reminderBox.classList.add('budget-exceeded');
        saveButton.disabled = true;
        showWarningMessage();
    } else {
        reminderBox.classList.remove('budget-exceeded');
        saveButton.disabled = false;
        hideWarningMessage();
    }
}
```

### 4. Controller Extensions

**PageController Updates**:

```php
// In PageController.php

public function preProject()
{
    $user = Auth::user();
    
    // Existing pre-project query logic...
    
    // Add budget data for Parliament/DUN users
    $budgetData = null;
    if ($this->budgetService->isSubjectToBudgetValidation($user)) {
        $budgetData = $this->budgetService->getUserBudgetData($user);
    }
    
    return view('pages.pre-project', compact('preProjects', 'budgetData', ...));
}

public function residenUsers()
{
    $user = Auth::user();
    
    // Existing residen users query logic...
    
    // Add budget overview for Residen users
    $budgetOverview = null;
    if ($user->residen_category_id) {
        $budgetOverview = $this->budgetService->getResidenBudgetOverview();
    }
    
    return view('pages.users-id.residen', compact('users', 'budgetOverview', ...));
}

public function preProjectStore(Request $request)
{
    $user = Auth::user();
    
    // Existing validation...
    
    // Budget validation for Parliament/DUN users
    if ($this->budgetService->isSubjectToBudgetValidation($user)) {
        $totalCost = $request->input('total_cost');
        
        if ($this->budgetService->wouldExceedBudget($user, $totalCost)) {
            return back()->withErrors([
                'budget' => 'Budget exceeded! You cannot create this pre-project as it exceeds your remaining budget.'
            ])->withInput();
        }
    }
    
    // Proceed with saving...
}
```

### 5. Database Schema

**Existing Tables Used**:

```sql
-- parliaments table
parliaments (
    id,
    name,
    code,
    budget DECIMAL(15,2),  -- Used for budget allocation
    status,
    created_at,
    updated_at
)

-- duns table
duns (
    id,
    name,
    code,
    parliament_id,
    budget DECIMAL(15,2),  -- Used for budget allocation
    status,
    created_at,
    updated_at
)

-- pre_projects table
pre_projects (
    id,
    parliament_id,
    dun_id,
    total_cost DECIMAL(15,2),  -- Used for budget calculation
    status,  -- 'Waiting for Approval', 'Approved', 'Rejected', etc.
    created_at,
    updated_at
)

-- users table
users (
    id,
    parliament_category_id,  -- Links to parliaments or duns
    residen_category_id,     -- Links to residen_categories
    created_at,
    updated_at
)
```

**No New Tables Required**: The system uses existing schema.

**Future Enhancement**: Multi-year budget tables (if implemented later):
```sql
-- parliament_budgets table (future)
parliament_budgets (
    id,
    parliament_id,
    year INT,
    budget DECIMAL(15,2),
    created_at,
    updated_at,
    UNIQUE(parliament_id, year)
)

-- dun_budgets table (future)
dun_budgets (
    id,
    dun_id,
    year INT,
    budget DECIMAL(15,2),
    created_at,
    updated_at,
    UNIQUE(dun_id, year)
)
```

## Data Models

### Budget Data Structure

**User Budget Data** (returned by `getUserBudgetData`):
```php
[
    'total_budget' => 5000000.00,      // float
    'total_allocated' => 3250000.00,   // float
    'remaining_budget' => 1750000.00,  // float
    'year' => 2026,                    // int
    'parliament_id' => 1,              // int|null
    'dun_id' => null,                  // int|null
]
```

**Residen Budget Overview** (returned by `getResidenBudgetOverview`):
```php
[
    'total_parliament_budget' => 50000000.00,  // float
    'total_dun_budget' => 30000000.00,         // float
    'total_allocated' => 45000000.00,          // float
    'overall_remaining' => 35000000.00,        // float
    'year' => 2026,                            // int
]
```

### Model Relationships

**User Model**:
```php
class User extends Model
{
    public function parliament()
    {
        return $this->belongsTo(Parliament::class, 'parliament_category_id');
    }
    
    public function dun()
    {
        return $this->belongsTo(Dun::class, 'parliament_category_id');
    }
    
    public function residenCategory()
    {
        return $this->belongsTo(ResidenCategory::class);
    }
    
    // Helper method
    public function isParliamentUser(): bool
    {
        return $this->parliament_category_id !== null && $this->parliament !== null;
    }
    
    public function isDunUser(): bool
    {
        return $this->parliament_category_id !== null && $this->dun !== null;
    }
    
    public function isResidenUser(): bool
    {
        return $this->residen_category_id !== null;
    }
}
```

**Parliament Model**:
```php
class Parliament extends Model
{
    protected $casts = [
        'budget' => 'decimal:2',
    ];
    
    public function preProjects()
    {
        return $this->hasMany(PreProject::class);
    }
    
    public function approvedPreProjects()
    {
        return $this->hasMany(PreProject::class)
            ->whereIn('status', ['Waiting for Approval', 'Approved']);
    }
}
```

**Dun Model**:
```php
class Dun extends Model
{
    protected $casts = [
        'budget' => 'decimal:2',
    ];
    
    public function preProjects()
    {
        return $this->hasMany(PreProject::class);
    }
    
    public function approvedPreProjects()
    {
        return $this->hasMany(PreProject::class)
            ->whereIn('status', ['Waiting for Approval', 'Approved']);
    }
}
```

**PreProject Model**:
```php
class PreProject extends Model
{
    protected $casts = [
        'total_cost' => 'decimal:2',
    ];
    
    public function parliament()
    {
        return $this->belongsTo(Parliament::class);
    }
    
    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }
    
    // Scope for budget calculation
    public function scopeAllocated($query)
    {
        return $query->whereIn('status', ['Waiting for Approval', 'Approved']);
    }
}
```

## Correctness Properties

A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.

### Property Reflection

After analyzing all acceptance criteria, I identified the following redundancies:
- Requirements 1.4 and 5.5 both test the same arithmetic (remaining = total - allocated)
- Requirements 4.2 and 5.6 both test Parliament budget summation
- Requirements 4.3 and 5.7 both test DUN budget summation
- Requirements 4.4 and 5.8 both test total allocated calculation for Residen users
- Requirements 1.6 and 7.5 both test year format display

These redundant properties have been consolidated into single comprehensive properties below.

### Property 1: Total Budget Display Accuracy

*For any* Parliament or DUN user, the displayed Total Budget value should equal the budget value stored in their associated Parliament or DUN record.

**Validates: Requirements 1.2**

### Property 2: Total Allocated Calculation Accuracy

*For any* Parliament or DUN user, the displayed Total Allocated value should equal the sum of total_cost for all pre-projects with status "Waiting for Approval" or "Approved" that belong to their Parliament or DUN.

**Validates: Requirements 1.3, 5.3, 5.4**

### Property 3: Remaining Budget Arithmetic

*For any* budget data, the Remaining Budget should equal Total Budget minus Total Allocated.

**Validates: Requirements 1.4, 5.5**

### Property 4: Negative Budget Styling

*For any* budget data where Remaining Budget is negative, the Remaining Budget box should have red styling applied (CSS class or inline style).

**Validates: Requirements 1.5**

### Property 5: Year Format Display

*For any* year value, the displayed budget year label should match the format "Budget for Year YYYY".

**Validates: Requirements 1.6, 7.5**

### Property 6: Currency Format Display

*For any* numeric currency value, the formatted output should match the pattern "RM X,XXX,XXX.XX" with proper thousand separators and two decimal places.

**Validates: Requirements 1.7, 2.3**

### Property 7: Real-time Budget Update

*For any* cost input change in the Create/Edit modal, the budget reminder display should update to reflect the new remaining budget calculation.

**Validates: Requirements 2.4**

### Property 8: Budget Exceeded Color Change

*For any* cost input that would cause (Remaining Budget - Cost) to be negative, the budget reminder box should display with red styling.

**Validates: Requirements 2.5**

### Property 9: Budget Within Limit Color

*For any* cost input that would result in (Remaining Budget - Cost) being zero or positive, the budget reminder box should display with green styling.

**Validates: Requirements 2.6**

### Property 10: New Total Allocated Calculation

*For any* pre-project save attempt, the calculated new Total Allocated should equal Current Total Allocated plus the pre-project total_cost.

**Validates: Requirements 3.1**

### Property 11: Budget Exceeded Button Disable

*For any* scenario where new Total Allocated exceeds Total Budget, the Save or Create Pre-Project button should be disabled.

**Validates: Requirements 3.2, 3.4**

### Property 12: Budget Exceeded Warning Display

*For any* scenario where budget is exceeded, a warning message "Budget exceeded! You cannot create this pre-project as it exceeds your remaining budget." should be displayed.

**Validates: Requirements 3.3**

### Property 13: Residen User Budget Validation Exemption

*For any* user with residen_category_id set, budget validation should not be applied when creating or editing pre-projects.

**Validates: Requirements 3.5, 6.6**

### Property 14: Budget Within Limit Button Enable

*For any* scenario where new Total Allocated is less than or equal to Total Budget, the Save or Create Pre-Project button should be enabled.

**Validates: Requirements 3.6**

### Property 15: Parliament Budget Aggregation

*For any* set of Parliament records, the Total Parliament Budget for Residen users should equal the sum of all budget values from the parliaments table.

**Validates: Requirements 4.2, 5.6**

### Property 16: DUN Budget Aggregation

*For any* set of DUN records, the Total DUN Budget for Residen users should equal the sum of all budget values from the duns table.

**Validates: Requirements 4.3, 5.7**

### Property 17: Residen Total Allocated Calculation

*For any* set of pre-projects across all Parliaments and DUNs, the Total Allocated for Residen users should equal the sum of total_cost for all pre-projects with status "Waiting for Approval" or "Approved".

**Validates: Requirements 4.4, 5.8**

### Property 18: Overall Remaining Calculation

*For any* Residen budget overview data, the Overall Remaining should equal (Total Parliament Budget + Total DUN Budget) - Total Allocated.

**Validates: Requirements 4.5**

### Property 19: Year Selector Data Update

*For any* year selection change by a Residen user, all budget overview boxes should display data filtered to the selected year.

**Validates: Requirements 4.8**

### Property 20: Parliament User Budget Source

*For any* user with parliament_category_id linked to a Parliament record, the budget data should be retrieved from the parliaments table.

**Validates: Requirements 5.1**

### Property 21: DUN User Budget Source

*For any* user with parliament_category_id linked to a DUN record, the budget data should be retrieved from the duns table.

**Validates: Requirements 5.2**

### Property 22: Parliament User Budget Scope

*For any* Parliament user, budget boxes should display data filtered to only their Parliament (not other Parliaments or DUNs).

**Validates: Requirements 6.1**

### Property 23: DUN User Budget Scope

*For any* DUN user, budget boxes should display data filtered to only their DUN (not other DUNs or Parliaments).

**Validates: Requirements 6.2**

### Property 24: Residen User Aggregated Display

*For any* user with residen_category_id, the budget overview should display aggregated data from all Parliaments and DUNs.

**Validates: Requirements 6.3**

### Property 25: Non-Authorized User No Display

*For any* user without parliament_category_id or residen_category_id, budget boxes should not be displayed on any page.

**Validates: Requirements 6.4**

### Property 26: Budget Validation Scope

*For any* user with parliament_category_id, budget validation should be applied when creating or editing pre-projects.

**Validates: Requirements 6.5**

### Property 27: Default Year Display

*For any* budget display where no year is explicitly specified, the system should use the current calendar year.

**Validates: Requirements 7.1**

### Property 28: Year-Filtered Pre-Project Calculation

*For any* year parameter, the Total Allocated calculation should only include pre-projects created within that budget year.

**Validates: Requirements 7.4**

## Error Handling

### Budget Calculation Errors

**Null Budget Values**:
- If Parliament or DUN budget is null, treat as 0.00
- Log warning for missing budget data
- Display "Budget not set" message to user

**Database Query Failures**:
- Catch database exceptions in BudgetCalculationService
- Return default budget data structure with zeros
- Log error with context (user ID, query details)
- Display generic error message to user

**Invalid User Scope**:
- If user has neither parliament_category_id nor residen_category_id, return null budget data
- Do not display budget boxes
- Log warning if budget data is requested for invalid user type

### Validation Errors

**Budget Exceeded**:
- Return validation error with specific message
- Preserve user input for correction
- Highlight budget reminder in red
- Disable save button via JavaScript

**Concurrent Budget Updates**:
- Use database transactions for pre-project creation
- Re-check budget after transaction lock
- If budget exceeded due to concurrent save, rollback and show error
- Suggest user refresh page to see updated budget

**Invalid Cost Values**:
- Validate cost is numeric and positive
- Validate cost does not exceed reasonable limits (e.g., 1 billion)
- Return validation error with specific message

### JavaScript Errors

**Budget Calculator Failures**:
- Wrap all JavaScript in try-catch blocks
- Log errors to console
- Fallback to server-side validation only
- Display message: "Real-time budget calculation unavailable"

**DOM Element Not Found**:
- Check for element existence before manipulation
- Log warning if expected elements missing
- Gracefully degrade to non-interactive display

## Testing Strategy

### Dual Testing Approach

The budget tracking system requires both unit tests and property-based tests for comprehensive coverage:

**Unit Tests** focus on:
- Specific budget calculation examples (e.g., budget = 5M, allocated = 3M, remaining = 2M)
- Edge cases (null budgets, zero budgets, negative remaining)
- Error conditions (database failures, invalid user types)
- Integration points (controller methods, view rendering)

**Property-Based Tests** focus on:
- Universal properties across all budget values (arithmetic correctness)
- Budget calculations for randomly generated user/project data
- Validation logic across various cost inputs
- Aggregation calculations for random sets of Parliaments/DUNs

### Property-Based Testing Configuration

**Framework**: Use Laravel's built-in testing with a PHP property-based testing library (e.g., Eris or php-quickcheck)

**Test Configuration**:
- Minimum 100 iterations per property test
- Each test tagged with feature name and property number
- Tag format: `@test Feature: pre-project-budget-tracking, Property {N}: {property_text}`

**Example Property Test**:
```php
/**
 * @test
 * Feature: pre-project-budget-tracking, Property 3: Remaining Budget Arithmetic
 */
public function test_remaining_budget_equals_total_minus_allocated()
{
    $this->forAll(
        Generator::float(0, 100000000), // total_budget
        Generator::float(0, 100000000)  // total_allocated
    )->then(function ($totalBudget, $totalAllocated) {
        $remainingBudget = $totalBudget - $totalAllocated;
        
        $budgetData = [
            'total_budget' => $totalBudget,
            'total_allocated' => $totalAllocated,
            'remaining_budget' => $remainingBudget
        ];
        
        $this->assertEquals(
            $totalBudget - $totalAllocated,
            $budgetData['remaining_budget'],
            'Remaining budget should equal total minus allocated'
        );
    });
}
```

### Unit Test Examples

**Budget Calculation Service Tests**:
```php
public function test_parliament_user_budget_data_retrieval()
{
    $parliament = Parliament::factory()->create(['budget' => 5000000]);
    $user = User::factory()->create(['parliament_category_id' => $parliament->id]);
    
    PreProject::factory()->create([
        'parliament_id' => $parliament->id,
        'total_cost' => 2000000,
        'status' => 'Approved'
    ]);
    
    $service = new BudgetCalculationService();
    $budgetData = $service->getUserBudgetData($user);
    
    $this->assertEquals(5000000, $budgetData['total_budget']);
    $this->assertEquals(2000000, $budgetData['total_allocated']);
    $this->assertEquals(3000000, $budgetData['remaining_budget']);
}

public function test_budget_validation_prevents_overspending()
{
    $parliament = Parliament::factory()->create(['budget' => 1000000]);
    $user = User::factory()->create(['parliament_category_id' => $parliament->id]);
    
    PreProject::factory()->create([
        'parliament_id' => $parliament->id,
        'total_cost' => 800000,
        'status' => 'Approved'
    ]);
    
    $service = new BudgetCalculationService();
    $wouldExceed = $service->wouldExceedBudget($user, 300000);
    
    $this->assertTrue($wouldExceed, 'Should detect budget would be exceeded');
}

public function test_residen_user_exempted_from_validation()
{
    $user = User::factory()->create(['residen_category_id' => 1]);
    
    $service = new BudgetCalculationService();
    $isSubject = $service->isSubjectToBudgetValidation($user);
    
    $this->assertFalse($isSubject, 'Residen users should be exempt from validation');
}
```

**Controller Integration Tests**:
```php
public function test_pre_project_list_displays_budget_boxes_for_parliament_user()
{
    $parliament = Parliament::factory()->create(['budget' => 5000000]);
    $user = User::factory()->create(['parliament_category_id' => $parliament->id]);
    
    $response = $this->actingAs($user)->get('/pages/pre-project');
    
    $response->assertStatus(200);
    $response->assertSee('TOTAL BUDGET');
    $response->assertSee('RM 5,000,000.00');
}

public function test_budget_exceeded_prevents_pre_project_creation()
{
    $parliament = Parliament::factory()->create(['budget' => 1000000]);
    $user = User::factory()->create(['parliament_category_id' => $parliament->id]);
    
    PreProject::factory()->create([
        'parliament_id' => $parliament->id,
        'total_cost' => 900000,
        'status' => 'Approved'
    ]);
    
    $response = $this->actingAs($user)->post('/pages/pre-project', [
        'total_cost' => 200000,
        // ... other fields
    ]);
    
    $response->assertSessionHasErrors('budget');
    $this->assertDatabaseCount('pre_projects', 1); // Only the existing one
}
```

### JavaScript Testing

**Budget Calculator Tests** (using Jest or similar):
```javascript
describe('Budget Calculator', () => {
    test('updates remaining budget in real-time', () => {
        const remainingBudget = 1000000;
        const projectCost = 300000;
        
        updateBudgetReminder(remainingBudget, projectCost);
        
        const reminderBox = document.getElementById('budget-reminder');
        expect(reminderBox.textContent).toContain('RM 700,000.00');
    });
    
    test('disables save button when budget exceeded', () => {
        const remainingBudget = 1000000;
        const projectCost = 1500000;
        
        updateBudgetReminder(remainingBudget, projectCost);
        
        const saveButton = document.getElementById('save-button');
        expect(saveButton.disabled).toBe(true);
    });
});
```

### Test Coverage Goals

- **Service Layer**: 100% code coverage for BudgetCalculationService
- **Controllers**: 90%+ coverage for budget-related controller methods
- **Components**: 100% coverage for budget-box and budget-reminder components
- **JavaScript**: 90%+ coverage for budget calculator functions
- **Integration**: All user workflows tested end-to-end

