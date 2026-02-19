# Parliament/DUN Budget Tracking Fix

## Issue
Parliament and DUN users tidak nampak budget boxes (TOTAL BUDGET, TOTAL ALLOCATED, REMAINING BUDGET) di Pre-Project page, walaupun Residen users boleh nampak 5 boxes.

## Root Cause
`BudgetCalculationService::getUserBudgetData()` method return `null` untuk non-Parliament/DUN users, tetapi view check `isset($budgetInfo) && $budgetInfo['source_name']` yang akan fail kalau `$budgetInfo` is `null`.

## Solution Implemented

### 1. Fixed BudgetCalculationService
**File**: `app/Services/BudgetCalculationService.php`

Changed `getUserBudgetData()` to ALWAYS return an array instead of `null`:

```php
// Before: return null for non-Parliament/DUN users
if (!$user->parliament_category_id && !$user->dun_id) {
    return null;
}

// After: return empty array with source_name
if (!$user->parliament_category_id && !$user->dun_id) {
    return [
        'total_budget' => 0.0,
        'total_allocated' => 0.0,
        'remaining_budget' => 0.0,
        'year' => $year,
        'parliament_id' => null,
        'dun_id' => null,
        'source_name' => '',
    ];
}
```

Also fixed the case when Parliament/DUN record not found:
```php
if (!$parliamentId && !$dunId) {
    // Before: return null
    // After: return empty array
    return [
        'total_budget' => 0.0,
        'total_allocated' => 0.0,
        'remaining_budget' => 0.0,
        'year' => $year,
        'parliament_id' => null,
        'dun_id' => null,
        'source_name' => '',
    ];
}
```

## How It Works Now

### For Parliament Users
1. User login dengan `parliament_category_id`
2. System fetch budget dari `parliament_budgets` table untuk current year
3. Calculate total allocated dari `pre_projects` where `parliament_id` matches
4. Display 3 budget boxes: TOTAL BUDGET, TOTAL ALLOCATED, REMAINING BUDGET

### For DUN Users
1. User login dengan `dun_id`
2. System fetch budget dari `dun_budgets` table untuk current year
3. Calculate total allocated dari `pre_projects` where `dun_id` matches
4. Display 3 budget boxes: TOTAL BUDGET, TOTAL ALLOCATED, REMAINING BUDGET

### For Residen Users
1. User login dengan `residen_category_id`
2. System calculate aggregated budgets across ALL Parliament and DUN
3. Display 5 budget boxes:
   - TOTAL BUDGET PARLIAMENT
   - TOTAL BUDGET DUN
   - TOTAL ALLOCATED PARLIAMENT
   - TOTAL ALLOCATED DUN
   - REMAINING BUDGET

## Budget Tracking Features

### 1. Budget Boxes Display
- **Location**: Above Pre-Project table
- **Parliament/DUN**: 3 boxes in 1 row
- **Residen**: 5 boxes in 1 row

### 2. Budget Reminder in Create Modal
- **Location**: Inside "Cost of Project" section, below Total Cost field
- **Display**: Tiny simple box showing remaining budget
- **Colors**:
  - Green: Budget OK
  - Yellow: Budget warning (close to limit)
  - Red: Budget exceeded

### 3. Budget Validation
- **Create button disabled** if budget is 0
- **Save/Create button disabled** if total cost exceeds remaining budget
- **Real-time calculation** as user enters cost values
- **Warning message** displayed when budget exceeded

## Testing

### Test Case 1: Parliament User
1. Login as Parliament user (e.g., khairun90@sarawak.gov.my)
2. Navigate to Pre-Project page
3. ✅ Should see 3 budget boxes
4. Click Create Pre-Project
5. ✅ Should see budget reminder in modal
6. Enter cost that exceeds budget
7. ✅ Save button should be disabled

### Test Case 2: DUN User
1. Login as DUN user
2. Navigate to Pre-Project page
3. ✅ Should see 3 budget boxes
4. Click Create Pre-Project
5. ✅ Should see budget reminder in modal

### Test Case 3: Residen User
1. Login as Residen user
2. Navigate to Pre-Project page
3. ✅ Should see 5 budget boxes
4. ✅ No budget validation (can create unlimited)

## Files Modified
- `app/Services/BudgetCalculationService.php` - Fixed getUserBudgetData() to always return array

## Related Components
- `resources/views/components/budget-box.blade.php` - Budget box component for Parliament/DUN
- `resources/views/components/residen-budget-box.blade.php` - Budget box component for Residen
- `resources/views/pages/pre-project.blade.php` - Pre-Project page with budget boxes

## Status
✅ **FIXED** - Parliament/DUN users can now see budget boxes and budget tracking works correctly
