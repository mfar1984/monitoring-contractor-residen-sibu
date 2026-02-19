# Design Document: Multi-Year Budget Allocation

## Overview

The Multi-Year Budget Allocation system replaces the single-budget field model with a flexible multi-year budget management system. This design introduces new database tables for storing year-budget pairs, updates the BudgetCalculationService to handle year-based calculations, and provides dynamic UI controls for managing multiple budget entries.

### Key Design Decisions

1. **Separate Budget Tables**: Create dedicated `parliament_budgets` and `dun_budgets` tables instead of storing JSON in existing tables for better queryability and data integrity
2. **Year Range**: Support years 2024-2030 initially, easily extendable in configuration
3. **Cascade Deletion**: Automatically delete budget entries when parent Parliament/DUN is deleted
4. **Current Year Default**: Use current calendar year as default when no year is specified
5. **JavaScript-Based Dynamic Rows**: Use vanilla JavaScript for add/remove functionality to avoid framework dependencies
6. **Backward Compatibility**: Migrate existing budget data to current year entries

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Parliament Form  │  DUN Form  │  Pre-Project Form          │
│  (Dynamic Rows)   │ (Dynamic)  │  (Year Selection)          │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Service Layer                            │
├─────────────────────────────────────────────────────────────┤
│         BudgetCalculationService (Year-Aware)                │
│  - getUserBudgetInfo($user, $year)                          │
│  - calculateAllocatedBudget($entity, $year)                 │
│  - isWithinBudget($entity, $amount, $year)                  │
│  - getAvailableBudgetForEdit($project, $year)               │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Data Layer                               │
├─────────────────────────────────────────────────────────────┤
│  Parliament Model  │  Dun Model  │  PreProject Model        │
│  (budgets relation)│ (budgets)   │  (year-based validation) │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Database Layer                           │
├─────────────────────────────────────────────────────────────┤
│  parliaments  │  parliament_budgets  │  duns  │ dun_budgets│
│  pre_projects (with project_year)                           │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### Database Schema

#### parliament_budgets Table

```sql
CREATE TABLE parliament_budgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parliament_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    budget DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (parliament_id) REFERENCES parliaments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_parliament_year (parliament_id, year),
    INDEX idx_parliament_year (parliament_id, year)
);
```

#### dun_budgets Table

```sql
CREATE TABLE dun_budgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dun_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    budget DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (dun_id) REFERENCES duns(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dun_year (dun_id, year),
    INDEX idx_dun_year (dun_id, year)
);
```

### Model Relationships

#### Parliament Model

```php
class Parliament extends Model
{
    protected $fillable = ['name', 'code', 'description', 'status'];
    
    public function budgets()
    {
        return $this->hasMany(ParliamentBudget::class);
    }
    
    public function getBudgetForYear($year)
    {
        return $this->budgets()
            ->where('year', $year)
            ->first()?->budget ?? 0;
    }
    
    public function getAllYears()
    {
        return $this->budgets()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
    }
}
```

#### Dun Model

```php
class Dun extends Model
{
    protected $fillable = ['parliament_id', 'name', 'code', 'description', 'status'];
    
    public function budgets()
    {
        return $this->hasMany(DunBudget::class);
    }
    
    public function getBudgetForYear($year)
    {
        return $this->budgets()
            ->where('year', $year)
            ->first()?->budget ?? 0;
    }
    
    public function getAllYears()
    {
        return $this->budgets()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
    }
}
```

#### ParliamentBudget Model

```php
class ParliamentBudget extends Model
{
    protected $fillable = ['parliament_id', 'year', 'budget'];
    
    protected $casts = [
        'budget' => 'decimal:2',
        'year' => 'integer',
    ];
    
    public function parliament()
    {
        return $this->belongsTo(Parliament::class);
    }
}
```

#### DunBudget Model

```php
class DunBudget extends Model
{
    protected $fillable = ['dun_id', 'year', 'budget'];
    
    protected $casts = [
        'budget' => 'decimal:2',
        'year' => 'integer',
    ];
    
    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }
}
```

## Data Models

### Budget Entry Structure

```php
[
    'year' => 2024,        // YEAR type (4 digits)
    'budget' => 5000000.00 // DECIMAL(15,2)
]
```

### Form Data Structure

When submitting Parliament/DUN forms:

```php
[
    'name' => 'Parliament Name',
    'code' => 'P001',
    'status' => 'Active',
    'budgets' => [
        ['year' => 2024, 'budget' => 5000000.00],
        ['year' => 2025, 'budget' => 6000000.00],
        ['year' => 2026, 'budget' => 5500000.00],
    ]
]
```

### Budget Calculation Response

```php
[
    'total_budget' => 5000000.00,
    'allocated_budget' => 3200000.00,
    'remaining_budget' => 1800000.00,
    'year' => 2024,
    'has_budget' => true
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: Budget amount validation

*For any* budget entry submission, if the budget amount is positive and has at most 15 digits with 2 decimal places, then the system should accept it; otherwise it should reject it.

**Validates: Requirements 1.6, 8.5, 8.6**

### Property 2: Parliament year uniqueness

*For any* Parliament, attempting to create multiple budget entries with the same year should be rejected by the system.

**Validates: Requirements 1.7, 2.5**

### Property 3: DUN year uniqueness

*For any* DUN, attempting to create multiple budget entries with the same year should be rejected by the system.

**Validates: Requirements 1.8, 2.6**

### Property 4: Cascade deletion for Parliament budgets

*For any* Parliament with associated budget entries, when the Parliament is deleted, all its budget entries should also be deleted from the database.

**Validates: Requirements 2.3**

### Property 5: Cascade deletion for DUN budgets

*For any* DUN with associated budget entries, when the DUN is deleted, all its budget entries should also be deleted from the database.

**Validates: Requirements 2.4**

### Property 6: Parliament total budget calculation

*For any* Parliament and year, the calculated total budget should equal the sum of all budget entry amounts for that specific year.

**Validates: Requirements 3.1**

### Property 7: DUN total budget calculation

*For any* DUN and year, the calculated total budget should equal the sum of all budget entry amounts for that specific year.

**Validates: Requirements 3.2**

### Property 8: Allocated budget excludes cancelled and rejected projects

*For any* entity (Parliament or DUN) and year, the calculated allocated budget should equal the sum of total_cost for all pre-projects in that year, excluding projects with status "Cancelled" or "Rejected".

**Validates: Requirements 3.3, 4.5**

### Property 9: Remaining budget calculation

*For any* entity (Parliament or DUN) and year, the remaining budget should equal the total budget minus the allocated budget for that year.

**Validates: Requirements 3.4**

### Property 10: Pre-project budget validation uses correct year

*For any* pre-project, budget validation should check the total_cost against the remaining budget for the pre-project's specific project_year, not any other year.

**Validates: Requirements 3.6**

### Property 11: Pre-project creation budget validation

*For any* pre-project being created, if its total_cost exceeds the remaining budget for its project_year, the system should reject the creation.

**Validates: Requirements 4.1, 4.3**

### Property 12: Pre-project edit budget validation excludes self

*For any* pre-project being edited, the budget validation should calculate remaining budget by excluding the current project's existing cost, then validate the new total_cost against that remaining budget.

**Validates: Requirements 4.2**

### Property 13: Budget Box year filtering

*For any* Budget_Box component with a year parameter, the displayed total budget, allocated budget, and remaining budget should all be calculated using only data for that specific year.

**Validates: Requirements 5.1, 5.2, 5.3**

### Property 14: Budget entries sorted by year

*For any* display of budget entries (Parliament or DUN), the entries should be ordered in ascending order by year.

**Validates: Requirements 7.7**

### Property 15: Required field validation

*For any* form submission with empty year or budget fields, the system should reject the submission and indicate which fields are empty.

**Validates: Requirements 8.3, 8.4**

## Error Handling

### Validation Errors

**Duplicate Year Error**:
- Message: "The year {year} already exists for this {Parliament/DUN}. Each year can only be added once."
- HTTP Status: 422 Unprocessable Entity
- Return user to form with entered data preserved

**Budget Exceeded Error**:
- Message: "The project cost of RM {amount} exceeds the remaining budget of RM {remaining} for year {year}."
- HTTP Status: 422 Unprocessable Entity
- Display available budget in error message

**No Budget for Year Error**:
- Message: "No budget has been allocated for year {year}. Please contact your administrator."
- HTTP Status: 422 Unprocessable Entity
- Prevent form submission

**Invalid Budget Amount Error**:
- Message: "Budget amount must be a positive number with up to 15 digits and 2 decimal places."
- HTTP Status: 422 Unprocessable Entity
- Highlight invalid field

**Missing Budget Entries Error**:
- Message: "At least one year-budget entry is required."
- HTTP Status: 422 Unprocessable Entity
- Highlight budget section

### Database Errors

**Foreign Key Constraint Error**:
- Scenario: Attempting to create budget entry for non-existent Parliament/DUN
- Handling: Log error, return 500 Internal Server Error
- User Message: "An error occurred while saving budget data. Please try again."

**Unique Constraint Violation**:
- Scenario: Database-level duplicate year detection
- Handling: Catch exception, return user-friendly message
- User Message: "This year already exists for this {Parliament/DUN}."

### Migration Errors

**Missing Column Error**:
- Scenario: Migration runs but budget column doesn't exist
- Handling: Skip migration for records without budget column, log warning
- Continue with other records

**Data Type Conversion Error**:
- Scenario: Existing budget value cannot be converted to decimal
- Handling: Log error with record ID, set budget to 0, continue migration
- Generate migration report with errors

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests for comprehensive coverage:

**Unit Tests**: Focus on specific examples, edge cases, and integration points
- Test specific year ranges (2024-2030)
- Test form submission with one budget entry
- Test form submission with multiple budget entries
- Test delete button disabled state with one entry
- Test Budget Box with current year default
- Test migration with sample data
- Test rollback functionality

**Property-Based Tests**: Verify universal properties across all inputs
- Generate random budget amounts and validate acceptance/rejection
- Generate random Parliament/DUN data with duplicate years
- Generate random pre-projects and verify budget calculations
- Generate random budget entries and verify sorting
- Generate random form data and verify validation

### Property-Based Testing Configuration

**Library**: Use `fakerphp/faker` for data generation and PHPUnit for assertions
**Iterations**: Minimum 100 iterations per property test
**Tag Format**: Each test must include a comment:
```php
/**
 * Feature: multi-year-budget-allocation, Property 2: Parliament year uniqueness
 * For any Parliament, attempting to create multiple budget entries with the same year should be rejected
 */
```

### Test Coverage Requirements

**Service Layer Tests**:
- BudgetCalculationService::getUserBudgetInfo() with various years
- BudgetCalculationService::calculateAllocatedBudget() with mixed project statuses
- BudgetCalculationService::isWithinBudget() with edge cases
- BudgetCalculationService::getAvailableBudgetForEdit() excluding current project

**Model Tests**:
- Parliament::getBudgetForYear() with existing and non-existing years
- Dun::getBudgetForYear() with existing and non-existing years
- Cascade deletion for Parliament and DUN
- Unique constraint enforcement

**Controller Tests**:
- Parliament form submission with valid budget entries
- DUN form submission with duplicate years
- Pre-project creation with insufficient budget
- Pre-project edit with budget validation

**Integration Tests**:
- End-to-end flow: Create Parliament → Add budgets → Create pre-project
- End-to-end flow: Edit pre-project → Verify budget recalculation
- Migration flow: Old data → New structure → Verify integrity

### Edge Cases to Test

1. **Zero Budget**: Parliament/DUN with budget of 0 for a year
2. **No Budget Entry**: Attempting to create pre-project for year with no budget
3. **Exact Budget Match**: Pre-project cost exactly equals remaining budget
4. **Single Entry**: Form with only one budget entry (delete button disabled)
5. **Maximum Digits**: Budget amount with exactly 15 digits
6. **Decimal Precision**: Budget amount with exactly 2 decimal places
7. **Year Boundaries**: Years 2024 (minimum) and 2030 (maximum)
8. **Current Year**: Default behavior when no year specified
9. **All Projects Cancelled**: Allocated budget should be 0
10. **Mixed Statuses**: Pre-projects with various statuses in budget calculation

## Implementation Notes

### JavaScript Dynamic Row Management

```javascript
// Add new budget entry row
function addBudgetRow() {
    const container = document.getElementById('budget-entries');
    const rowCount = container.children.length;
    const newRow = createBudgetRow(rowCount);
    container.appendChild(newRow);
    updateDeleteButtons();
}

// Remove budget entry row
function removeBudgetRow(index) {
    const container = document.getElementById('budget-entries');
    if (container.children.length > 1) {
        container.children[index].remove();
        updateDeleteButtons();
        reindexRows();
    }
}

// Disable delete button if only one entry
function updateDeleteButtons() {
    const container = document.getElementById('budget-entries');
    const deleteButtons = container.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.disabled = container.children.length === 1;
    });
}
```

### Service Method Updates

```php
// BudgetCalculationService.php

public function getUserBudgetInfo($user, $year = null)
{
    $year = $year ?? now()->year;
    
    if ($user->parliament_id) {
        $parliament = Parliament::find($user->parliament_id);
        return $this->calculateBudgetInfo($parliament, 'parliament', $year);
    }
    
    if ($user->dun_id) {
        $dun = Dun::find($user->dun_id);
        return $this->calculateBudgetInfo($dun, 'dun', $year);
    }
    
    return null;
}

private function calculateBudgetInfo($entity, $type, $year)
{
    $totalBudget = $entity->getBudgetForYear($year);
    $allocatedBudget = $this->calculateAllocatedBudget($entity, $type, $year);
    $remainingBudget = $totalBudget - $allocatedBudget;
    
    return [
        'total_budget' => $totalBudget,
        'allocated_budget' => $allocatedBudget,
        'remaining_budget' => $remainingBudget,
        'year' => $year,
        'has_budget' => $totalBudget > 0,
    ];
}

private function calculateAllocatedBudget($entity, $type, $year)
{
    $query = PreProject::where('project_year', $year)
        ->whereNotIn('status', ['Cancelled', 'Rejected']);
    
    if ($type === 'parliament') {
        $query->where('parliament_id', $entity->id);
    } else {
        $query->where('dun_id', $entity->id);
    }
    
    return $query->sum('total_cost');
}
```

### Migration Strategy

```php
// Migration: Create budget tables and migrate data
public function up()
{
    // Create parliament_budgets table
    Schema::create('parliament_budgets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('parliament_id')->constrained()->onDelete('cascade');
        $table->year('year');
        $table->decimal('budget', 15, 2);
        $table->timestamps();
        
        $table->unique(['parliament_id', 'year']);
        $table->index(['parliament_id', 'year']);
    });
    
    // Create dun_budgets table
    Schema::create('dun_budgets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('dun_id')->constrained()->onDelete('cascade');
        $table->year('year');
        $table->decimal('budget', 15, 2);
        $table->timestamps();
        
        $table->unique(['dun_id', 'year']);
        $table->index(['dun_id', 'year']);
    });
    
    // Migrate existing budget data to current year
    $currentYear = now()->year;
    
    DB::table('parliaments')->whereNotNull('budget')->each(function ($parliament) use ($currentYear) {
        DB::table('parliament_budgets')->insert([
            'parliament_id' => $parliament->id,
            'year' => $currentYear,
            'budget' => $parliament->budget,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });
    
    DB::table('duns')->whereNotNull('budget')->each(function ($dun) use ($currentYear) {
        DB::table('dun_budgets')->insert([
            'dun_id' => $dun->id,
            'year' => $currentYear,
            'budget' => $dun->budget,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });
    
    // Remove old budget columns
    Schema::table('parliaments', function (Blueprint $table) {
        $table->dropColumn('budget');
    });
    
    Schema::table('duns', function (Blueprint $table) {
        $table->dropColumn('budget');
    });
}

public function down()
{
    // Restore budget columns
    Schema::table('parliaments', function (Blueprint $table) {
        $table->decimal('budget', 15, 2)->nullable();
    });
    
    Schema::table('duns', function (Blueprint $table) {
        $table->decimal('budget', 15, 2)->nullable();
    });
    
    // Restore budget data from current year
    $currentYear = now()->year;
    
    DB::table('parliament_budgets')->where('year', $currentYear)->each(function ($budget) {
        DB::table('parliaments')
            ->where('id', $budget->parliament_id)
            ->update(['budget' => $budget->budget]);
    });
    
    DB::table('dun_budgets')->where('year', $currentYear)->each(function ($budget) {
        DB::table('duns')
            ->where('id', $budget->dun_id)
            ->update(['budget' => $budget->budget]);
    });
    
    // Drop budget tables
    Schema::dropIfExists('dun_budgets');
    Schema::dropIfExists('parliament_budgets');
}
```

### Form Validation Rules

```php
// Parliament/DUN Form Request

public function rules()
{
    return [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50',
        'status' => 'required|in:Active,Inactive',
        'budgets' => 'required|array|min:1',
        'budgets.*.year' => 'required|integer|min:2024|max:2030',
        'budgets.*.budget' => 'required|numeric|min:0|max:9999999999999.99',
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $budgets = $this->input('budgets', []);
        $years = array_column($budgets, 'year');
        
        if (count($years) !== count(array_unique($years))) {
            $validator->errors()->add('budgets', 'Each year can only be added once.');
        }
    });
}
```

### Budget Box Component Update

```blade
{{-- resources/views/components/budget-box.blade.php --}}

@props(['year' => null])

@php
    $year = $year ?? now()->year;
    $budgetInfo = app(\App\Services\BudgetCalculationService::class)
        ->getUserBudgetInfo(Auth::user(), $year);
@endphp

<div class="budget-summary">
    <div class="budget-year">Budget for Year {{ $year }}</div>
    
    @if($budgetInfo && $budgetInfo['has_budget'])
        <div class="budget-boxes">
            <div class="budget-box total">
                <div class="budget-label">Total Budget</div>
                <div class="budget-amount">RM {{ number_format($budgetInfo['total_budget'], 2) }}</div>
            </div>
            
            <div class="budget-box allocated">
                <div class="budget-label">Total Allocated</div>
                <div class="budget-amount">RM {{ number_format($budgetInfo['allocated_budget'], 2) }}</div>
            </div>
            
            <div class="budget-box remaining">
                <div class="budget-label">Remaining Budget</div>
                <div class="budget-amount">RM {{ number_format($budgetInfo['remaining_budget'], 2) }}</div>
            </div>
        </div>
    @else
        <div class="budget-warning">
            <span class="material-symbols-outlined">warning</span>
            No budget allocated for year {{ $year }}
        </div>
    @endif
</div>
```
