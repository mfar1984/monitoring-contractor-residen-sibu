# Implementation Plan: Pre-Project Budget Tracking

## Overview

This implementation plan breaks down the Pre-Project Budget Tracking feature into discrete coding tasks. The feature adds real-time budget monitoring to the pre-project system, displaying budget information above the table and within the create/edit modal. Implementation follows Laravel best practices with service layer architecture, Blade components, and client-side JavaScript for real-time feedback.

## Tasks

- [x] 1. Create Budget Calculation Service
  - Create `app/Services/BudgetCalculationService.php`
  - Implement `getUserBudgetInfo()` method to retrieve budget data for logged-in user
  - Implement `calculateAllocatedBudget()` method to sum pre-project costs excluding Cancelled/Rejected
  - Implement `isWithinBudget()` method to validate cost against remaining budget
  - Implement `getAvailableBudgetForEdit()` method for edit operations
  - _Requirements: 1.3, 2.2, 2.3, 2.4, 5.1, 5.2, 5.3, 5.4, 5.5, 8.1, 8.2_

- [ ]* 1.1 Write property test for budget calculation accuracy
  - **Property 2: Budget Calculation Accuracy**
  - **Validates: Requirements 2.3, 5.3, 5.4, 5.5**

- [ ]* 1.2 Write property test for remaining budget arithmetic
  - **Property 3: Remaining Budget Arithmetic**
  - **Validates: Requirements 2.4**

- [ ]* 1.3 Write property test for user budget identification
  - **Property 1: User Budget Identification**
  - **Validates: Requirements 1.3, 5.1, 5.2**

- [ ]* 1.4 Write property test for edit mode available budget calculation
  - **Property 11: Edit Mode Available Budget Calculation**
  - **Validates: Requirements 8.1, 8.2**

- [x] 2. Create Budget Box Blade Component
  - Create `resources/views/components/budget-box.blade.php`
  - Accept props: totalBudget, allocatedBudget, remainingBudget, sourceName
  - Display three rows: Total Budget, Total Allocated, Remaining Budget
  - Apply conditional CSS classes based on budget status (sufficient/exceeded)
  - Format all amounts with RM prefix and 2 decimal places
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.8, 7.2_

- [ ]* 2.1 Write property test for budget display completeness
  - **Property 4: Budget Display Completeness**
  - **Validates: Requirements 2.2, 7.2**

- [ ]* 2.2 Write property test for currency formatting consistency
  - **Property 5: Currency Formatting Consistency**
  - **Validates: Requirements 2.8**

- [ ]* 2.3 Write property test for conditional styling
  - **Property 6: Conditional Styling Based on Budget Status**
  - **Validates: Requirements 2.5, 2.6, 3.5, 3.6**

- [x] 3. Create Budget Box CSS Styling
  - Create `public/css/components/budget-box.css`
  - Define `.budget-box` base styles with gradient background
  - Define `.budget-sufficient` class with green gradient (#28a745 to #1e7e34)
  - Define `.budget-exceeded` class with red gradient (#dc3545 to #bd2130)
  - Define `.budget-row` flex layout for label-value pairs
  - Define `.budget-remaining` bold styling for emphasis
  - Ensure responsive design for mobile devices
  - _Requirements: 2.5, 2.6, 7.1, 7.2, 7.5, 7.6, 7.7_

- [x] 4. Update PageController to provide budget data
  - Modify `app/Http/Controllers/Pages/PageController.php`
  - Update `preProject()` method to instantiate BudgetCalculationService
  - Call `getUserBudgetInfo()` and pass budget data to view
  - Ensure budget data is available for both create and edit modals
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2_

- [x] 5. Integrate Budget Box into Pre-Project List Page
  - Modify `resources/views/pages/pre-project.blade.php`
  - Add Budget Box component above the data-table component
  - Pass budget data from controller to component
  - Load budget-box.css in the layout file
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.8_

- [x] 6. Add Budget Reminder to Create Modal
  - Modify `resources/views/pages/pre-project.blade.php` (create modal section)
  - Add budget reminder div in the Cost of Project form group
  - Display initial remaining budget from controller data
  - Add data attributes to cost input field (remaining-budget, original-cost)
  - Style budget reminder as compact inline component
  - _Requirements: 3.1, 3.2, 3.7_

- [x] 7. Implement Real-Time Budget Calculation JavaScript
  - Add JavaScript in pre-project.blade.php modal script section
  - Add event listener to cost input field for 'input' event
  - Calculate new remaining budget on each input change
  - Update budget reminder display with new value
  - Apply conditional CSS classes (budget-ok, budget-exceeded)
  - Update Save button state (enabled/disabled) based on budget
  - Ensure no server requests during calculation
  - _Requirements: 3.3, 3.4, 3.5, 3.6, 4.1, 4.4, 4.6, 6.1, 6.4_

- [ ]* 7.1 Write JavaScript property test for real-time calculation
  - **Property 7: Real-Time Budget Calculation**
  - **Validates: Requirements 3.3, 3.4, 6.1, 6.4**

- [ ]* 7.2 Write JavaScript property test for button state management
  - **Property 8: Budget Validation Button State**
  - **Validates: Requirements 4.1, 4.4, 4.6, 8.4**

- [x] 8. Add Budget Exceeded Error Message Display
  - Modify budget reminder HTML to include error message element
  - Update JavaScript to show/hide error message based on budget status
  - Format error message: "Budget exceeded. Remaining budget: RM X.XX"
  - Style error message with red color and appropriate spacing
  - _Requirements: 4.2, 4.3_

- [ ]* 8.1 Write property test for budget exceeded error message
  - **Property 9: Budget Exceeded Error Message**
  - **Validates: Requirements 4.2, 4.3**

- [x] 9. Create Form Request for Pre-Project Store Validation
  - Create `app/Http/Requests/StorePreProjectRequest.php`
  - Add validation rule for total_cost field
  - Implement custom validation closure using BudgetCalculationService
  - Check if cost is within budget using `isWithinBudget()` method
  - Return formatted error message with remaining budget amount
  - _Requirements: 4.5, 6.5_

- [ ]* 9.1 Write property test for server-side budget validation
  - **Property 10: Server-Side Budget Validation**
  - **Validates: Requirements 4.5, 6.5**

- [x] 10. Create Form Request for Pre-Project Update Validation
  - Create `app/Http/Requests/UpdatePreProjectRequest.php`
  - Add validation rule for total_cost field
  - Implement custom validation closure for edit operations
  - Use `getAvailableBudgetForEdit()` to calculate available budget
  - Return formatted error message: "Available for this project: RM X.XX"
  - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [ ]* 10.1 Write property test for edit mode budget message
  - **Property 12: Edit Mode Budget Message**
  - **Validates: Requirements 8.3**

- [x] 11. Update PageController to use Form Requests
  - Modify `preProjectStore()` method to use StorePreProjectRequest
  - Modify `preProjectUpdate()` method to use UpdatePreProjectRequest
  - Ensure validation errors are returned to view with old input
  - _Requirements: 4.5, 6.5, 8.4_

- [x] 12. Add Budget Reminder for Edit Modal
  - Modify edit modal section in pre-project.blade.php
  - Add budget reminder with edit-specific message
  - Set data-original-cost attribute to current project cost
  - Update JavaScript to handle edit mode calculation
  - Display "Available for this project: RM X.XX" message
  - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [x] 13. Implement Budget Recalculation After Edit
  - Ensure controller redirects to pre-project list after successful edit
  - Budget Box will automatically show updated values on page load
  - Verify budget data is recalculated using fresh database query
  - _Requirements: 8.5_

- [ ]* 13.1 Write property test for budget recalculation after edit
  - **Property 13: Budget Recalculation After Edit**
  - **Validates: Requirements 8.5**

- [x] 14. Add Error Handling for Missing Budget Data
  - Update BudgetCalculationService to handle null budget values
  - Return 0 for budget if Parliament/DUN has no budget set
  - Add warning message in Budget Box when budget is 0
  - Log warning for admin review when budget is missing
  - _Requirements: Error Handling - Missing Budget Data_

- [x] 15. Add Error Handling for Budget Calculation Failures
  - Wrap budget calculations in try-catch blocks
  - Return default values (0) on calculation errors
  - Log errors to Laravel log file
  - Display user-friendly error message in Budget Box
  - _Requirements: Error Handling - Database Query Failure_

- [x] 16. Checkpoint - Test budget display and validation
  - Verify Budget Box displays correctly above pre-project table
  - Test budget reminder updates in real-time as cost is entered
  - Verify Save button is disabled when budget is exceeded
  - Test server-side validation rejects over-budget submissions
  - Verify error messages display correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 17. Add CSS for Budget Reminder Component
  - Add budget reminder styles to `public/css/components/forms.css`
  - Define `.budget-reminder` base styles
  - Define `.budget-ok` class with green color scheme
  - Define `.budget-exceeded` class with red color scheme
  - Define `.button-disabled` class for grayed-out Save button
  - Ensure compact, non-intrusive layout
  - _Requirements: 3.7, 4.6_

- [x] 18. Handle Edge Case: Zero Budget Allocation
  - Update Budget Box to show clear message when budget is 0
  - Disable pre-project creation when budget is 0
  - Display "No budget available. Contact administrator." message
  - Prevent modal from opening when budget is 0
  - _Requirements: Error Handling - Zero Budget Allocation_

- [x] 19. Handle Edge Case: Negative Remaining Budget
  - Update Budget Box to display negative values in red
  - Show warning message when remaining budget is negative
  - Prevent new pre-project creation when budget is negative
  - Allow viewing existing projects but block new submissions
  - _Requirements: Error Handling - Negative Remaining Budget_

- [x] 20. Add Decimal Precision Handling in JavaScript
  - Update JavaScript calculation to use toFixed(2) for all amounts
  - Ensure consistent 2-decimal display in budget reminder
  - Handle floating-point arithmetic precision issues
  - Test with various decimal values to ensure accuracy
  - _Requirements: Error Handling - Decimal Precision Issues_

- [ ]* 21. Write integration test for full create workflow
  - Test: View page → Open modal → Enter cost → Submit → Verify budget update
  - Verify budget validation across create operations
  - Test error handling and user feedback
  - Ensure budget recalculation after successful creation

- [ ]* 22. Write integration test for full edit workflow
  - Test: View page → Open edit modal → Change cost → Submit → Verify budget update
  - Verify available budget calculation includes original cost
  - Test budget validation for edit operations
  - Ensure budget recalculation after successful edit

- [ ]* 23. Write integration test for budget exceeded scenario
  - Test user cannot create pre-project exceeding budget
  - Verify validation error is displayed
  - Test user cannot edit pre-project to exceed available budget
  - Verify error messages contain correct remaining budget amounts

- [x] 24. Update Layout to Load Budget Box CSS
  - Modify `resources/views/layouts/app.blade.php`
  - Add link tag for budget-box.css in the head section
  - Ensure CSS loads before page content renders
  - _Requirements: 2.1, 7.1_

- [x] 25. Add Documentation Comments to Service Methods
  - Add PHPDoc comments to all BudgetCalculationService methods
  - Document parameters, return types, and exceptions
  - Add usage examples in comments
  - Document edge cases and assumptions

- [x] 26. Final Checkpoint - Comprehensive Testing
  - Run all property-based tests (minimum 100 iterations each)
  - Run all integration tests
  - Run JavaScript tests for real-time calculation
  - Test on different browsers (Chrome, Firefox, Safari)
  - Test responsive design on mobile devices
  - Verify all error handling scenarios
  - Check Laravel logs for any warnings or errors
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties with 100+ iterations
- Unit tests validate specific examples and edge cases
- JavaScript tests use Jest or Vitest for client-side validation
- Budget calculations are performed both client-side (UX) and server-side (security)
- All budget amounts use decimal(15,2) precision
- CSS follows existing component structure in `public/css/components/`
- Service layer pattern maintains separation of concerns
