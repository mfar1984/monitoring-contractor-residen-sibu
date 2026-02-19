# Implementation Plan: Residen Budget Boxes

## Overview

This implementation plan breaks down the Residen budget boxes feature into discrete coding tasks. Each task builds on previous steps and includes specific file modifications, method implementations, and testing requirements. The implementation follows Laravel best practices and maintains consistency with the existing codebase.

## Tasks

- [x] 1. Extend BudgetCalculationService with Residen aggregation method
  - Add `getResidenBudgetInfo()` method to `app/Services/BudgetCalculationService.php`
  - Implement four aggregate queries: Parliament budgets, DUN budgets, Parliament allocations, DUN allocations
  - Calculate remaining budget from totals
  - Add error handling with try-catch blocks and logging
  - Return array with all required budget values
  - _Requirements: 2.1, 2.2, 3.1, 3.2, 4.1, 4.2, 4.3, 5.1, 5.2, 5.3, 6.1, 9.1, 9.2, 9.4, 9.5_

- [ ]* 1.1 Write property test for budget sum accuracy
  - **Property 1: Budget Calculation Accuracy**
  - **Validates: Requirements 2.1, 3.1**
  - Generate random Parliament and DUN budgets for a fiscal year
  - Verify sum of total_budget_parliament + total_budget_dun equals expected total
  - Run 100 iterations with different random data

- [ ]* 1.2 Write property test for cancelled projects exclusion
  - **Property 2: Allocated Budget Exclusion**
  - **Validates: Requirements 4.2, 5.2**
  - Generate pre-projects with various statuses including "Cancelled" and "Rejected"
  - Verify allocated totals exclude Cancelled and Rejected projects
  - Run 100 iterations with different status combinations

- [ ]* 1.3 Write property test for remaining budget formula
  - **Property 3: Remaining Budget Calculation**
  - **Validates: Requirements 6.1**
  - Generate random budgets and allocations
  - Verify remaining_budget = (total_budget_parliament + total_budget_dun) - (total_allocated_parliament + total_allocated_dun)
  - Run 100 iterations with different values

- [ ]* 1.4 Write unit tests for BudgetCalculationService
  - Test return array structure and data types
  - Test Parliament budget calculation
  - Test DUN budget calculation
  - Test year filtering
  - Test null/empty budget handling
  - Test error handling
  - _Requirements: 2.1, 2.2, 3.1, 3.2, 7.1, 9.4_

- [x] 2. Create Residen budget box Blade component
  - Create `resources/views/components/residen-budget-box.blade.php`
  - Accept `year` prop (optional, defaults to current year)
  - Call BudgetCalculationService::getResidenBudgetInfo() to get data
  - Render 5 budget boxes in grid layout (grid-template-columns: repeat(5, 1fr))
  - Use existing budget-box CSS classes (budget-item, budget-label, budget-value)
  - Apply conditional red color for negative remaining budget
  - Add year header showing "Residen Budget Overview for Year {year}"
  - _Requirements: 1.1, 1.4, 2.3, 2.4, 3.3, 3.4, 4.4, 4.5, 5.4, 5.5, 6.2, 6.3, 6.4, 6.5, 7.2, 7.3, 8.1, 8.2, 8.3_

- [ ]* 2.1 Write property test for visual consistency
  - **Property 7: Visual Consistency**
  - **Validates: Requirements 1.4, 8.2**
  - Verify all budget boxes use correct CSS classes
  - Verify grid layout structure is correct
  - Run 100 iterations with different budget values

- [ ]* 2.2 Write property test for null handling
  - **Property 8: Null Handling**
  - **Validates: Requirements 2.2, 3.2, 4.3, 5.3, 9.4**
  - Generate years with no budget records
  - Verify component displays RM 0.00 for all values
  - Verify no errors are thrown
  - Run 100 iterations with different empty scenarios

- [x] 3. Modify PageController to pass Residen budget data
  - Update `preProject()` method in `app/Http/Controllers/Pages/PageController.php`
  - Add conditional check for `$user->residen_category_id`
  - Call `BudgetCalculationService::getResidenBudgetInfo($user)` if Residen user
  - Pass `$residenBudgetInfo` to view in compact() array
  - Ensure null is passed for non-Residen users
  - _Requirements: 1.1, 1.2, 7.1_

- [ ]* 3.1 Write integration test for Residen user sees budget boxes
  - Create test user with residen_category_id
  - Create sample budget data
  - Visit pre-project page
  - Assert Residen budget boxes are visible in response
  - _Requirements: 1.1_

- [ ]* 3.2 Write integration test for non-Residen user does not see budget boxes
  - Create test user without residen_category_id (Parliament or DUN user)
  - Visit pre-project page
  - Assert Residen budget boxes are NOT in response
  - _Requirements: 1.2_

- [x] 4. Update pre-project page to conditionally render Residen budget boxes
  - Modify `resources/views/pages/pre-project.blade.php`
  - Add conditional check: `@if($user->residen_category_id && isset($residenBudgetInfo))`
  - Include Residen budget box component: `<x-residen-budget-box :year="$residenBudgetInfo['year'] ?? now()->year" />`
  - Position below existing budget-box component
  - Ensure proper spacing and layout
  - _Requirements: 1.1, 1.2, 1.3, 7.2, 7.4, 8.4_

- [ ]* 4.1 Write property test for conditional display
  - **Property 4: Conditional Display**
  - **Validates: Requirements 1.2**
  - Generate users with and without residen_category_id
  - Verify Residen budget boxes only render for Residen users
  - Run 100 iterations with different user types

- [ ]* 4.2 Write integration test for budget values display correctly
  - Create known budget data (specific amounts)
  - Create Residen user
  - Visit pre-project page
  - Assert displayed values match expected calculations
  - _Requirements: 2.1, 2.4, 3.1, 3.4, 4.1, 4.5, 5.1, 5.5, 6.1, 6.5_

- [ ]* 4.3 Write integration test for negative remaining budget displays in red
  - Create over-budget scenario (allocations > budgets)
  - Create Residen user
  - Visit pre-project page
  - Assert remaining budget value has red color styling
  - _Requirements: 6.2_

- [ ] 5. Add property test for year filtering consistency
  - **Property 5: Year Consistency**
  - **Validates: Requirements 7.1, 7.4**
  - Create budgets for multiple years (2023, 2024, 2025)
  - Create pre-projects for multiple years
  - For each year, call getResidenBudgetInfo()
  - Verify only that year's data is included
  - Verify no cross-year contamination
  - Run 100 iterations with different year combinations
  - _Requirements: 7.1, 7.4_

- [ ] 6. Add property test for non-negative values
  - **Property 6: Non-Negative Budget Values**
  - **Validates: Requirements 2.2, 3.2, 4.3, 5.3**
  - Generate random positive budgets and allocations
  - Call getResidenBudgetInfo()
  - Assert total_budget_parliament >= 0
  - Assert total_budget_dun >= 0
  - Assert total_allocated_parliament >= 0
  - Assert total_allocated_dun >= 0
  - Note: remaining_budget CAN be negative (over-budget scenario)
  - Run 100 iterations with different values
  - _Requirements: 2.2, 3.2, 4.3, 5.3_

- [ ] 7. Checkpoint - Ensure all tests pass
  - Run `php artisan test` to verify all unit and integration tests pass
  - Run property-based tests with 100 iterations each
  - Verify no errors in Laravel logs
  - Test manually in browser as Residen user
  - Test manually in browser as non-Residen user
  - Verify budget calculations are accurate
  - Verify visual styling matches existing budget boxes
  - Ask the user if questions arise

- [x] 8. Final integration and documentation
  - Clear all caches: `php artisan cache:clear`, `php artisan config:clear`, `php artisan view:clear`
  - Verify feature works in development environment
  - Check browser console for JavaScript errors (should be none)
  - Verify responsive layout on different screen sizes
  - Confirm no N+1 query issues (use Laravel Debugbar if available)
  - Update any relevant documentation or comments
  - _Requirements: 8.3, 9.1, 9.3_

## Notes

- Tasks marked with `*` are optional property-based and unit tests that can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests should run minimum 100 iterations to ensure comprehensive coverage
- All property tests must include the feature name and property number in comments
- The implementation uses existing infrastructure (BudgetCalculationService, budget-box styling) to minimize code changes
- No database migrations are required - feature uses existing tables
- No new routes or API endpoints are needed
- Feature is purely display-focused with no user input or form submissions
