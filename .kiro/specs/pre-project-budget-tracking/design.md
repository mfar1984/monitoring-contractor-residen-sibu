# Design Document: Pre-Project Budget Tracking

## Overview

The Pre-Project Budget Tracking feature provides real-time budget monitoring and validation for Parliament and DUN constituencies. The system displays budget information in two key locations: a prominent budget box above the pre-project table and an inline budget reminder within the create/edit modal. All calculations are performed both client-side (for immediate feedback) and server-side (for validation), ensuring data integrity while providing excellent user experience.

The design follows the existing Laravel application architecture with Blade templates, component-based UI, and service layer for business logic. Budget calculations leverage existing database relationships between users, parliaments/duns, and pre-projects.

## Architecture

### System Components

1. **Budget Calculation Service** (`BudgetCalculationService`)
   - Centralized budget calculation logic
   - Handles Parliament and DUN budget retrieval
   - Calculates allocated and remaining budgets
   - Filters out cancelled/rejected projects

2. **Budget Display Components**
   - Budget Box Blade component (above table)
   - Budget Reminder inline component (in modal)
   - Shared styling and color logic

3. **Client-Side Budget Validator** (JavaScript)
   - Real-time cost input monitoring
   - Dynamic remaining budget calculation
   - Button state management (enable/disable)
   - Visual feedback (color changes)

4. **Server-Side Budget Validator** (Form Request)
   - Final validation before database commit
   - Prevents budget overruns via backend logic
   - Returns clear error messages

### Data Flow

```
User Views Page → Controller fetches budget data → BudgetCalculationService calculates
→ Budget Box displays in Blade view

User Opens Modal → Budget Reminder shows current remaining budget
→ User enters cost → JavaScript recalculates → UI updates (color, button state)
→ User submits → Server validates → Success or Error response
```

### Integration Points

- **Existing Tables**: `parliaments`, `duns`, `pre_projects`, `users`
- **Existing Controllers**: `PageController` (pre-project methods)
- **Existing Models**: `Parliament`, `Dun`, `PreProject`, `User`
- **Existing Views**: `pages/pre-project.blade.php`
- **Existing CSS**: `public/css/app.css`, `public/css/components/forms.css`

## Components and Interfaces

### 1. BudgetCalculationService

**Location**: `app/Services/BudgetCalculationService.php`

**Purpose**: Centralized service for all budget-related calculations

**Methods**:

```php
class BudgetCalculationService
{
    /**
     * Get budget information for a user
     * 
     * @param User $user
     * @return array ['total_budget', 'allocated_budget', 'remaining_budget', 'source_type', 'source_name']
     */
    public function getUserBudgetInfo(User $user): array
    
    /**
     * Calculate total allocated budget for Parliament or DUN
     * 
     * @param string $type 'parliament' or 'dun'
     * @param int $id Parliament or DUN ID
     * @return float
     */
    public function calculateAllocatedBudget(string $type, int $id): float
    
    /**
     * Check if a cost amount is within remaining budget
     * 
     * @param User $user
     * @param float $cost
     * @param int|null $excludePreProjectId For edit operations
     * @return bool
     */
    public function isWithinBudget(User $user, float $cost, ?int $excludePreProjectId = null): bool
    
    /**
     * Get available budget for editing a pre-project
     * 
     * @param User $user
     * @param PreProject $preProject
     * @return float
     */
    public function getAvailableBudgetForEdit(User $user, PreProject $preProject): float
}
```

**Logic Details**:

- `getUserBudgetInfo()`: 
  - Check if user has `parliament_id` or `dun_id`
  - Fetch budget from respective table
  - Calculate allocated budget using `calculateAllocatedBudget()`
  - Return remaining budget (total - allocated)

- `calculateAllocatedBudget()`:
  - Query `pre_projects` table
  - Filter by `parliament_id` or `dun_id`
  - Exclude statuses: 'Cancelled', 'Rejected'
  - Sum `total_cost` column
  - Return total

- `isWithinBudget()`:
  - Get current remaining budget
  - If editing, add back the original project cost
  - Check if new cost <= available budget
  - Return boolean

### 2. Budget Box Component

**Location**: `resources/views/components/budget-box.blade.php`

**Purpose**: Display budget summary above pre-project table

**Props**:
- `totalBudget` (float): Total allocated budget
- `allocatedBudget` (float): Currently allocated amount
- `remainingBudget` (float): Remaining available budget
- `sourceName` (string): Parliament or DUN name

**Template Structure**:

```blade
<div class="budget-box {{ $remainingBudget < 0 ? 'budget-exceeded' : 'budget-sufficient' }}">
    <div class="budget-header">
        <h3>Budget Summary - {{ $sourceName }}</h3>
    </div>
    <div class="budget-content">
        <div class="budget-row">
            <span class="budget-label">Total Budget:</span>
            <span class="budget-value">RM {{ number_format($totalBudget, 2) }}</span>
        </div>
        <div class="budget-row">
            <span class="budget-label">Total Allocated:</span>
            <span class="budget-value">RM {{ number_format($allocatedBudget, 2) }}</span>
        </div>
        <div class="budget-row budget-remaining">
            <span class="budget-label">Remaining Budget:</span>
            <span class="budget-value">RM {{ number_format($remainingBudget, 2) }}</span>
        </div>
    </div>
</div>
```

**CSS Classes** (in `public/css/components/budget-box.css`):
- `.budget-box`: Container with gradient background
- `.budget-sufficient`: Green gradient (#28a745 to #1e7e34)
- `.budget-exceeded`: Red gradient (#dc3545 to #bd2130)
- `.budget-row`: Flex layout for label-value pairs
- `.budget-remaining`: Bold styling for emphasis

### 3. Budget Reminder Component

**Location**: Inline in `resources/views/pages/pre-project.blade.php` (within modal)

**Purpose**: Show real-time budget feedback during cost entry

**HTML Structure**:

```html
<div class="form-group">
    <label for="total_cost">Cost of Project (RM) <span class="required">*</span></label>
    <input type="number" 
           id="total_cost" 
           name="total_cost" 
           step="0.01" 
           min="0"
           data-remaining-budget="{{ $remainingBudget }}"
           data-original-cost="{{ $preProject->total_cost ?? 0 }}">
    
    <div id="budget-reminder" class="budget-reminder budget-ok">
        <span class="budget-icon">💰</span>
        <span id="budget-text">Remaining budget: RM <span id="budget-amount">{{ number_format($remainingBudget, 2) }}</span></span>
    </div>
</div>
```

**JavaScript Logic** (in modal script section):

```javascript
const costInput = document.getElementById('total_cost');
const budgetReminder = document.getElementById('budget-reminder');
const budgetAmount = document.getElementById('budget-amount');
const saveButton = document.getElementById('save-button');

const remainingBudget = parseFloat(costInput.dataset.remainingBudget);
const originalCost = parseFloat(costInput.dataset.originalCost || 0);
const availableBudget = remainingBudget + originalCost;

costInput.addEventListener('input', function() {
    const enteredCost = parseFloat(this.value) || 0;
    const newRemaining = availableBudget - enteredCost;
    
    // Update display
    budgetAmount.textContent = newRemaining.toFixed(2);
    
    // Update styling
    if (newRemaining < 0) {
        budgetReminder.classList.remove('budget-ok');
        budgetReminder.classList.add('budget-exceeded');
        saveButton.disabled = true;
        saveButton.classList.add('button-disabled');
    } else {
        budgetReminder.classList.remove('budget-exceeded');
        budgetReminder.classList.add('budget-ok');
        saveButton.disabled = false;
        saveButton.classList.remove('button-disabled');
    }
});
```

### 4. Controller Updates

**Location**: `app/Http/Controllers/Pages/PageController.php`

**Method Updates**:

```php
public function preProject()
{
    $user = Auth::user();
    
    // Existing code for pre-projects...
    
    // Add budget calculation
    $budgetService = new BudgetCalculationService();
    $budgetInfo = $budgetService->getUserBudgetInfo($user);
    
    return view('pages.pre-project', [
        'preProjects' => $preProjects,
        'budgetInfo' => $budgetInfo,
        // ... other data
    ]);
}

public function preProjectStore(StorePreProjectRequest $request)
{
    // Budget validation happens in StorePreProjectRequest
    
    // Existing store logic...
}

public function preProjectUpdate(UpdatePreProjectRequest $request, $id)
{
    // Budget validation happens in UpdatePreProjectRequest
    
    // Existing update logic...
}
```

### 5. Form Request Validation

**Location**: `app/Http/Requests/StorePreProjectRequest.php`

**Validation Rules**:

```php
class StorePreProjectRequest extends FormRequest
{
    public function rules()
    {
        return [
            'total_cost' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $budgetService = new BudgetCalculationService();
                    $user = Auth::user();
                    
                    if (!$budgetService->isWithinBudget($user, $value)) {
                        $budgetInfo = $budgetService->getUserBudgetInfo($user);
                        $fail("Budget exceeded. Remaining budget: RM " . number_format($budgetInfo['remaining_budget'], 2));
                    }
                },
            ],
            // ... other rules
        ];
    }
}
```

**Location**: `app/Http/Requests/UpdatePreProjectRequest.php`

```php
class UpdatePreProjectRequest extends FormRequest
{
    public function rules()
    {
        return [
            'total_cost' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $budgetService = new BudgetCalculationService();
                    $user = Auth::user();
                    $preProject = PreProject::findOrFail($this->route('id'));
                    
                    $availableBudget = $budgetService->getAvailableBudgetForEdit($user, $preProject);
                    
                    if ($value > $availableBudget) {
                        $fail("Budget exceeded. Available for this project: RM " . number_format($availableBudget, 2));
                    }
                },
            ],
            // ... other rules
        ];
    }
}
```

## Data Models

### Existing Models (No Changes Required)

**Parliament Model** (`app/Models/Parliament.php`):
- Already has `budget` column (decimal 15,2)
- Relationship: `hasMany(PreProject::class)`

**Dun Model** (`app/Models/Dun.php`):
- Already has `budget` column (decimal 15,2)
- Relationship: `hasMany(PreProject::class)`

**PreProject Model** (`app/Models/PreProject.php`):
- Already has `total_cost` column (decimal 15,2)
- Already has `status` column
- Relationships: `belongsTo(Parliament::class)`, `belongsTo(Dun::class)`

**User Model** (`app/Models/User.php`):
- Already has `parliament_id` and `dun_id` columns
- Relationships: `belongsTo(Parliament::class)`, `belongsTo(Dun::class)`

### Budget Calculation Query

**SQL Logic** (implemented in BudgetCalculationService):

```sql
-- For Parliament budget
SELECT SUM(total_cost) 
FROM pre_projects 
WHERE parliament_id = ? 
  AND status NOT IN ('Cancelled', 'Rejected')

-- For DUN budget
SELECT SUM(total_cost) 
FROM pre_projects 
WHERE dun_id = ? 
  AND status NOT IN ('Cancelled', 'Rejected')
```

**Eloquent Implementation**:

```php
// Parliament
PreProject::where('parliament_id', $parliamentId)
    ->whereNotIn('status', ['Cancelled', 'Rejected'])
    ->sum('total_cost');

// DUN
PreProject::where('dun_id', $dunId)
    ->whereNotIn('status', ['Cancelled', 'Rejected'])
    ->sum('total_cost');
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified several areas of redundancy:

1. **Color scheme properties (2.5, 2.6, 3.5, 3.6)**: These can be consolidated into a single property about conditional styling based on budget status
2. **Button state properties (4.1, 4.4, 4.6, 8.4)**: These can be combined into one comprehensive property about button state management
3. **Budget source properties (5.1, 5.2)**: These are two branches of the same lookup logic and can be tested together
4. **Status filtering properties (5.4, 5.5)**: Both test exclusion logic and can be combined
5. **Message format properties (4.2, 4.3, 8.3)**: These test similar message formatting and can be consolidated

The following properties represent the unique, non-redundant validation requirements:

### Property 1: User Budget Identification

*For any* logged-in user with either a parliament_id or dun_id, the system should correctly identify and retrieve the budget from the corresponding Parliament or DUN record.

**Validates: Requirements 1.3, 5.1, 5.2**

### Property 2: Budget Calculation Accuracy

*For any* Parliament or DUN with associated pre-projects, the total allocated budget should equal the sum of all pre-project costs, excluding projects with status "Cancelled" or "Rejected".

**Validates: Requirements 2.3, 5.3, 5.4, 5.5**

### Property 3: Remaining Budget Arithmetic

*For any* user with a total budget and allocated budget, the remaining budget should always equal total budget minus allocated budget.

**Validates: Requirements 2.4**

### Property 4: Budget Display Completeness

*For any* user viewing the pre-project page, the budget box should display all three required values: Total Budget, Total Allocated, and Remaining Budget.

**Validates: Requirements 2.2, 7.2**

### Property 5: Currency Formatting Consistency

*For any* numeric budget value, the formatted output should contain the "RM" prefix and exactly 2 decimal places.

**Validates: Requirements 2.8**

### Property 6: Conditional Styling Based on Budget Status

*For any* budget scenario, if remaining budget is negative, the display should use red/error styling; if positive or zero, it should use green/success styling.

**Validates: Requirements 2.5, 2.6, 3.5, 3.6**

### Property 7: Real-Time Budget Calculation

*For any* cost value entered in the modal, the budget reminder should recalculate and display the new remaining budget as: current remaining budget minus entered cost, without making server requests.

**Validates: Requirements 3.3, 3.4, 6.1, 6.4**

### Property 8: Budget Validation Button State

*For any* cost entry, if the entered cost exceeds remaining budget, the Save button should be disabled with visual indication; if within budget, the button should be enabled.

**Validates: Requirements 4.1, 4.4, 4.6, 8.4**

### Property 9: Budget Exceeded Error Message

*For any* over-budget scenario, the system should display an error message containing "Budget exceeded" and the specific remaining budget amount formatted as "RM X.XX".

**Validates: Requirements 4.2, 4.3**

### Property 10: Server-Side Budget Validation

*For any* form submission where total cost exceeds remaining budget, the server should reject the submission and return a validation error.

**Validates: Requirements 4.5, 6.5**

### Property 11: Edit Mode Available Budget Calculation

*For any* pre-project being edited, the available budget for that project should equal the current remaining budget plus the original project cost.

**Validates: Requirements 8.1, 8.2**

### Property 12: Edit Mode Budget Message

*For any* pre-project being edited, the budget reminder should display "Available for this project: RM X.XX" where X.XX is the calculated available budget.

**Validates: Requirements 8.3**

### Property 13: Budget Recalculation After Edit

*For any* successful pre-project edit that changes the cost, the budget display should immediately reflect the new allocated and remaining budget values.

**Validates: Requirements 8.5**

## Error Handling

### Client-Side Error Handling

1. **Invalid Cost Input**
   - Scenario: User enters negative number or non-numeric value
   - Handling: HTML5 input validation prevents negative values; JavaScript treats invalid input as 0
   - User Feedback: Input field shows validation message

2. **Budget Calculation Failure**
   - Scenario: JavaScript calculation encounters undefined or null values
   - Handling: Default to 0 for missing values; log error to console
   - User Feedback: Display "Unable to calculate budget" message

3. **Network Timeout During Submission**
   - Scenario: Form submission takes too long
   - Handling: Show loading indicator; allow user to retry
   - User Feedback: "Submission in progress..." message

### Server-Side Error Handling

1. **Budget Exceeded on Submission**
   - Scenario: User submits form with cost exceeding budget
   - Handling: Form request validation fails; return to form with errors
   - User Feedback: Display validation error message with remaining budget amount
   - HTTP Status: 422 Unprocessable Entity

2. **Missing Budget Data**
   - Scenario: User's Parliament/DUN has no budget set
   - Handling: Treat as 0 budget; allow admin to set budget
   - User Feedback: Warning message "No budget allocated for your constituency"
   - Logging: Log warning for admin review

3. **Database Query Failure**
   - Scenario: Budget calculation query fails
   - Handling: Catch exception; return generic error
   - User Feedback: "Unable to load budget information. Please try again."
   - Logging: Log full exception with stack trace
   - HTTP Status: 500 Internal Server Error

4. **Concurrent Edit Conflict**
   - Scenario: Two users edit pre-projects simultaneously, causing budget miscalculation
   - Handling: Use database transactions; re-validate budget before commit
   - User Feedback: "Budget has changed. Please review and resubmit."
   - Recovery: Refresh budget display; allow user to adjust and resubmit

### Edge Cases

1. **Zero Budget Allocation**
   - Scenario: Parliament/DUN has budget = 0
   - Handling: Display budget box with 0 values; prevent any pre-project creation
   - User Feedback: "No budget available. Contact administrator."

2. **Negative Remaining Budget (Historical Data)**
   - Scenario: Existing pre-projects exceed current budget (budget was reduced)
   - Handling: Display negative remaining budget in red; prevent new projects
   - User Feedback: "Budget exceeded. No new projects can be created."

3. **Very Large Budget Values**
   - Scenario: Budget exceeds typical display width
   - Handling: Use number formatting with commas; ensure responsive layout
   - User Feedback: Display full value with proper formatting (e.g., "RM 1,234,567.89")

4. **Decimal Precision Issues**
   - Scenario: JavaScript floating-point arithmetic causes precision errors
   - Handling: Round to 2 decimal places using toFixed(2)
   - User Feedback: Display consistent 2-decimal format

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests to ensure comprehensive coverage:

- **Unit tests**: Verify specific examples, edge cases, and error conditions
- **Property tests**: Verify universal properties across all inputs
- Both are complementary and necessary for comprehensive coverage

### Unit Testing Focus

Unit tests should focus on:
- Specific budget calculation examples (e.g., budget=10000, allocated=7500, remaining=2500)
- Edge cases (zero budget, negative remaining, very large numbers)
- Error conditions (missing budget data, invalid input)
- Integration points between components (controller → service → view)

Avoid writing too many unit tests for scenarios that property tests will cover (e.g., testing every possible budget combination).

### Property-Based Testing Configuration

**Library Selection**: 
- **PHP**: Use `pest-plugin-faker` or `phpunit-quickcheck` for property-based testing
- Minimum 100 iterations per property test

**Test Organization**:
- Create `tests/Feature/BudgetTracking/` directory
- One test file per property
- Tag each test with feature name and property number

**Property Test Examples**:

```php
// tests/Feature/BudgetTracking/BudgetCalculationAccuracyTest.php

/**
 * Feature: pre-project-budget-tracking, Property 2: Budget Calculation Accuracy
 * 
 * For any Parliament or DUN with associated pre-projects, the total allocated 
 * budget should equal the sum of all pre-project costs, excluding projects 
 * with status "Cancelled" or "Rejected".
 */
test('allocated budget equals sum of active pre-project costs', function () {
    // Run 100 iterations with random data
    for ($i = 0; $i < 100; $i++) {
        $parliament = Parliament::factory()->create(['budget' => fake()->randomFloat(2, 10000, 1000000)]);
        
        // Create random number of pre-projects with various statuses
        $activeProjects = PreProject::factory()
            ->count(fake()->numberBetween(1, 10))
            ->create([
                'parliament_id' => $parliament->id,
                'status' => fake()->randomElement(['Active', 'Pending', 'Approved']),
                'total_cost' => fake()->randomFloat(2, 1000, 50000)
            ]);
        
        // Create some cancelled/rejected projects (should be excluded)
        PreProject::factory()
            ->count(fake()->numberBetween(0, 5))
            ->create([
                'parliament_id' => $parliament->id,
                'status' => fake()->randomElement(['Cancelled', 'Rejected']),
                'total_cost' => fake()->randomFloat(2, 1000, 50000)
            ]);
        
        $budgetService = new BudgetCalculationService();
        $calculated = $budgetService->calculateAllocatedBudget('parliament', $parliament->id);
        
        $expected = $activeProjects->sum('total_cost');
        
        expect($calculated)->toBe($expected);
    }
})->repeat(100);
```

```php
// tests/Feature/BudgetTracking/RemainingBudgetArithmeticTest.php

/**
 * Feature: pre-project-budget-tracking, Property 3: Remaining Budget Arithmetic
 * 
 * For any user with a total budget and allocated budget, the remaining budget 
 * should always equal total budget minus allocated budget.
 */
test('remaining budget equals total minus allocated', function () {
    for ($i = 0; $i < 100; $i++) {
        $totalBudget = fake()->randomFloat(2, 10000, 1000000);
        $allocatedBudget = fake()->randomFloat(2, 0, $totalBudget);
        
        $parliament = Parliament::factory()->create(['budget' => $totalBudget]);
        $user = User::factory()->create(['parliament_id' => $parliament->id]);
        
        // Create pre-projects that sum to allocatedBudget
        PreProject::factory()->create([
            'parliament_id' => $parliament->id,
            'total_cost' => $allocatedBudget,
            'status' => 'Active'
        ]);
        
        $budgetService = new BudgetCalculationService();
        $budgetInfo = $budgetService->getUserBudgetInfo($user);
        
        $expectedRemaining = $totalBudget - $allocatedBudget;
        
        expect($budgetInfo['remaining_budget'])->toBe($expectedRemaining);
    }
})->repeat(100);
```

### JavaScript Testing

**Library**: Jest or Vitest for JavaScript property-based testing

**Test Focus**:
- Real-time calculation accuracy
- Button state transitions
- DOM updates without network requests
- Color class application based on budget status

**Example**:

```javascript
// tests/js/budgetReminder.test.js

/**
 * Feature: pre-project-budget-tracking, Property 7: Real-Time Budget Calculation
 */
describe('Budget Reminder Real-Time Calculation', () => {
  test('calculates remaining budget correctly for random inputs', () => {
    for (let i = 0; i < 100; i++) {
      const remainingBudget = Math.random() * 100000;
      const enteredCost = Math.random() * 150000;
      
      const expected = remainingBudget - enteredCost;
      const actual = calculateRemainingBudget(remainingBudget, enteredCost);
      
      expect(actual).toBeCloseTo(expected, 2);
    }
  });
});
```

### Integration Testing

**Focus Areas**:
- Full workflow: View page → Open modal → Enter cost → Submit → Verify budget update
- Budget validation across create and edit operations
- Error handling and user feedback
- Budget recalculation after successful operations

**Example Integration Test**:

```php
test('user cannot create pre-project exceeding budget', function () {
    $parliament = Parliament::factory()->create(['budget' => 10000]);
    $user = User::factory()->create(['parliament_id' => $parliament->id]);
    
    // Use up most of the budget
    PreProject::factory()->create([
        'parliament_id' => $parliament->id,
        'total_cost' => 9000,
        'status' => 'Active'
    ]);
    
    // Attempt to create project exceeding remaining budget
    $response = $this->actingAs($user)->post('/pages/pre-project', [
        'total_cost' => 2000, // Exceeds remaining 1000
        // ... other required fields
    ]);
    
    $response->assertSessionHasErrors('total_cost');
    expect($response->getSession()->get('errors')->first('total_cost'))
        ->toContain('Budget exceeded');
});
```

### Test Coverage Goals

- **Unit Tests**: 80%+ code coverage for BudgetCalculationService
- **Property Tests**: All 13 properties implemented with 100+ iterations each
- **Integration Tests**: Cover all user workflows (create, edit, view)
- **JavaScript Tests**: 80%+ coverage for budget reminder logic

### Continuous Integration

- Run all tests on every commit
- Property tests run in CI pipeline (may take longer due to iterations)
- JavaScript tests run separately with Node.js environment
- Generate coverage reports for review
