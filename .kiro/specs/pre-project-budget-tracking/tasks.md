# Implementation Plan: Pre-Project Budget Tracking

## Overview

This implementation plan breaks down the Pre-Project Budget Tracking feature into discrete coding tasks. The approach follows a layered architecture: (1) create the service layer for budget calculations, (2) build reusable Blade components, (3) integrate budget display into existing pages, (4) add validation logic, and (5) implement JavaScript for real-time updates. Each task builds incrementally to ensure the system remains functional throughout development.

## Tasks

- [x] 1. Create BudgetCalculationService with core calculation methods
  - Create `app/Services/BudgetCalculationService.php`
  - Implement `getUserBudgetData()` method for Parliament/DUN users
  - Implement `getResidenBudgetOverview()` method for Residen users
  - Implement `wouldExceedBudget()` method for validation
  - Implement `getRemainingBudgetAfter()` method for real-time calculations
  - Implement `isSubjectToBudgetValidation()` method for user role checking
  - Add proper error handling for null budgets and database failures
  - _Requirements: 1.2, 1.3, 1.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6.5, 6.6_

- [ ]* 1.1 Write property tests for BudgetCalculationService
  - **Property 3: Remaining Budget Arithmetic** - For any budget data, remaining should equal total minus allocated
  - **Property 10: New Total Allocated Calculation** - For any pre-project, new allocated should equal current plus project cost
  - **Property 15: Parliament Budget Aggregation** - For any set of Parliaments, total should equal sum of budgets
  - **Property 16: DUN Budget Aggregation** - For any set of DUNs, total should equal sum of budgets
  - **Property 17: Residen Total Allocated Calculation** - For any set of pre-projects, total should equal sum of costs
  - **Property 18: Overall Remaining Calculation** - For any Residen data, overall remaining should equal (parliament + dun) - allocated
  - **Validates: Requirements 1.4, 3.1, 4.2, 4.3, 4.4, 4.5, 5.5, 5.6, 5.7, 5.8**

- [ ]* 1.2 Write unit tests for BudgetCalculationService
  - Test Parliament user budget data retrieval with sample data
  - Test DUN user budget data retrieval with sample data
  - Test Residen user budget overview with sample data
  - Test budget validation prevents overspending
  - Test Residen users are exempted from validation
  - Test null budget values are handled gracefully
  - _Requirements: 1.2, 1.3, 1.4, 3.5, 5.1, 5.2, 6.6_

- [ ] 2. Add helper methods to User, Parliament, Dun, and PreProject models
  - Add `isParliamentUser()`, `isDunUser()`, `isResidenUser()` methods to User model
  - Add `approvedPreProjects()` relationship to Parliament model
  - Add `approvedPreProjects()` relationship to Dun model
  - Add `scopeAllocated()` query scope to PreProject model
  - Add proper casts for budget and total_cost fields
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3_

- [ ]* 2.1 Write unit tests for model helper methods
  - Test User model role detection methods
  - Test Parliament and Dun approved pre-projects relationships
  - Test PreProject allocated scope filters correctly
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3_

- [ ] 3. Create budget-box Blade component
  - Create `resources/views/components/budget-box.blade.php`
  - Accept props: title, amount, color, year, isNegative
  - Implement gradient background styling based on color prop
  - Format currency as "RM X,XXX,XXX.XX"
  - Apply red styling when isNegative is true
  - Make responsive (3 boxes per row on desktop, stacked on mobile)
  - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

- [ ]* 3.1 Write property tests for budget-box component
  - **Property 6: Currency Format Display** - For any numeric value, format should match "RM X,XXX,XXX.XX"
  - **Property 5: Year Format Display** - For any year, label should match "Budget for Year YYYY"
  - **Property 4: Negative Budget Styling** - For any negative remaining budget, red styling should be applied
  - **Validates: Requirements 1.5, 1.6, 1.7**

- [ ] 4. Create budget-reminder Blade component for modals
  - Create `resources/views/components/budget-reminder.blade.php`
  - Accept props: remainingBudget, projectCost
  - Display compact inline box below cost input fields
  - Show "Remaining Budget: RM X,XXX,XXX.XX" format
  - Add CSS classes for green (within budget) and red (exceeded) states
  - Position below cost input fields
  - _Requirements: 2.1, 2.2, 2.3, 2.7_

- [ ] 5. Integrate budget boxes into pre-project list page
  - Inject BudgetCalculationService into PageController
  - Update `preProject()` method to fetch budget data for Parliament/DUN users
  - Pass budget data to view
  - Add budget boxes above pre-project table in `resources/views/pages/pre-project.blade.php`
  - Display three boxes: Total Budget, Total Allocated, Remaining Budget
  - Apply conditional red color to Remaining Budget when negative
  - Only display for users with parliament_category_id
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 6.1, 6.2, 6.4, 7.1_

- [ ]* 5.1 Write integration tests for pre-project list page budget display
  - Test Parliament user sees budget boxes with correct data
  - Test DUN user sees budget boxes with correct data
  - Test user without parliament_category_id does not see budget boxes
  - Test negative remaining budget displays in red
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 6.1, 6.2, 6.4_

- [ ] 6. Integrate budget reminder into Create Pre-Project modal
  - Update pre-project create modal in `resources/views/pages/pre-project.blade.php`
  - Add budget-reminder component in Cost of Project section
  - Pass current remaining budget as prop
  - Add data attributes for JavaScript integration
  - Only display for Parliament/DUN users
  - _Requirements: 2.1, 2.3, 2.7, 6.1, 6.2_

- [ ] 7. Integrate budget reminder into Edit Pre-Project modal
  - Update pre-project edit modal in `resources/views/pages/pre-project.blade.php`
  - Add budget-reminder component in Cost of Project section
  - Calculate remaining budget excluding current pre-project cost
  - Pass adjusted remaining budget as prop
  - Add data attributes for JavaScript integration
  - Only display for Parliament/DUN users
  - _Requirements: 2.2, 2.3, 2.7, 6.1, 6.2_

- [ ] 8. Checkpoint - Ensure budget display is working
  - Verify budget boxes appear on pre-project list page for Parliament/DUN users
  - Verify budget reminder appears in create/edit modals
  - Verify currency formatting is correct
  - Verify negative budgets display in red
  - Ask user if questions arise

- [ ] 9. Implement JavaScript for real-time budget updates
  - Create `public/js/budget-calculator.js`
  - Implement `updateBudgetReminder()` function
  - Listen for input events on cost fields
  - Calculate new remaining budget in real-time
  - Update budget reminder text and color
  - Enable/disable save button based on budget status
  - Show/hide warning message when budget exceeded
  - _Requirements: 2.4, 2.5, 2.6, 3.2, 3.3, 3.4, 3.6_

- [ ]* 9.1 Write property tests for JavaScript budget calculator
  - **Property 7: Real-time Budget Update** - For any cost input change, display should update
  - **Property 8: Budget Exceeded Color Change** - For any cost causing negative remaining, color should be red
  - **Property 9: Budget Within Limit Color** - For any cost within budget, color should be green
  - **Property 11: Budget Exceeded Button Disable** - For any exceeded budget, button should be disabled
  - **Property 14: Budget Within Limit Button Enable** - For any budget within limit, button should be enabled
  - **Validates: Requirements 2.4, 2.5, 2.6, 3.2, 3.4, 3.6**

- [ ] 10. Add server-side budget validation to pre-project store method
  - Update `preProjectStore()` method in PageController
  - Check if user is subject to budget validation using service
  - Calculate if new pre-project would exceed budget
  - Return validation error if budget exceeded
  - Display error message: "Budget exceeded! You cannot create this pre-project as it exceeds your remaining budget."
  - Preserve user input for correction
  - Skip validation for Residen/Admin users
  - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.7, 6.5, 6.6_

- [ ] 11. Add server-side budget validation to pre-project update method
  - Update `preProjectUpdate()` method in PageController
  - Check if user is subject to budget validation using service
  - Exclude current pre-project cost from allocated calculation
  - Calculate if updated pre-project would exceed budget
  - Return validation error if budget exceeded
  - Display error message
  - Skip validation for Residen/Admin users
  - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.7, 6.5, 6.6_

- [ ]* 11.1 Write integration tests for budget validation
  - Test pre-project creation is prevented when budget exceeded
  - Test pre-project update is prevented when budget exceeded
  - Test Residen users can create pre-projects without budget validation
  - Test validation error message is displayed
  - Test user input is preserved after validation error
  - _Requirements: 3.1, 3.2, 3.3, 3.5, 6.5, 6.6_

- [ ] 12. Checkpoint - Ensure validation is working
  - Test creating pre-project within budget succeeds
  - Test creating pre-project exceeding budget fails with error
  - Test Residen user can create pre-project without budget check
  - Test real-time JavaScript updates work correctly
  - Ask user if questions arise

- [ ] 13. Integrate budget overview into Residen users page
  - Update `residenUsers()` method in PageController (or appropriate controller)
  - Fetch budget overview data for Residen users using service
  - Pass budget overview to view
  - Add four budget boxes at top of `resources/views/pages/users-id/residen.blade.php`
  - Display: Total Parliament Budget, Total DUN Budget, Total Allocated, Overall Remaining
  - Add year selector dropdown
  - Only display for users with residen_category_id
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 6.3_

- [ ]* 13.1 Write property tests for Residen budget overview
  - **Property 15: Parliament Budget Aggregation** - For any Parliaments, total should equal sum
  - **Property 16: DUN Budget Aggregation** - For any DUNs, total should equal sum
  - **Property 17: Residen Total Allocated Calculation** - For any pre-projects, total should equal sum
  - **Property 18: Overall Remaining Calculation** - For any data, overall remaining should equal (parliament + dun) - allocated
  - **Validates: Requirements 4.2, 4.3, 4.4, 4.5**

- [ ] 14. Implement year selector functionality for Residen page
  - Add JavaScript to handle year selector change event
  - Make AJAX request to fetch budget data for selected year
  - Update all four budget boxes with new data
  - Preserve selected year in session or URL parameter
  - _Requirements: 4.7, 4.8, 7.1_

- [ ]* 14.1 Write property tests for year selector
  - **Property 19: Year Selector Data Update** - For any year selection, data should be filtered to that year
  - **Property 27: Default Year Display** - When no year specified, current year should be used
  - **Property 28: Year-Filtered Pre-Project Calculation** - For any year, only that year's pre-projects should be included
  - **Validates: Requirements 4.8, 7.1, 7.4**

- [ ] 15. Add CSS styling for budget components
  - Create or update CSS file for budget box styling
  - Add gradient backgrounds for blue, green, yellow, red colors
  - Add responsive grid layout (3 boxes per row, stacked on mobile)
  - Add red text and border styling for negative budgets
  - Add budget-exceeded class styling for budget reminder
  - Add disabled button styling
  - Ensure consistent styling with existing design system
  - _Requirements: 1.5, 1.8, 2.5, 2.6, 3.4, 4.6_

- [ ] 16. Add error handling and logging
  - Add try-catch blocks in BudgetCalculationService methods
  - Log errors with context (user ID, query details)
  - Return default budget data structure on errors
  - Display user-friendly error messages
  - Handle null budget values gracefully
  - Add JavaScript error handling for budget calculator
  - _Requirements: All requirements (error handling is cross-cutting)_

- [ ] 17. Final checkpoint - End-to-end testing
  - Test complete workflow as Parliament user: view budget, create pre-project within budget
  - Test complete workflow as Parliament user: attempt to create pre-project exceeding budget
  - Test complete workflow as DUN user: view budget, create pre-project
  - Test complete workflow as Residen user: view budget overview, select different years
  - Test that users without roles do not see budget information
  - Verify all currency formatting is correct
  - Verify all calculations are accurate
  - Ensure all tests pass
  - Ask user if questions arise

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation throughout implementation
- Property tests validate universal correctness properties across all inputs
- Unit tests validate specific examples, edge cases, and integration points
- The implementation follows a bottom-up approach: service layer → components → integration → validation → JavaScript
- BudgetCalculationService is the core of the system and should be implemented first
- Budget validation is applied only to Parliament/DUN users, not Residen/Admin users
- Real-time JavaScript updates provide immediate feedback, but server-side validation is the final authority
