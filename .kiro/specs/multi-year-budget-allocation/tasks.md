# Implementation Plan: Multi-Year Budget Allocation

## Overview

This implementation plan transforms the single-budget model into a multi-year budget allocation system. The approach follows these phases: (1) Create database structure and models, (2) Update services for year-based calculations, (3) Implement dynamic UI forms, (4) Update budget validation logic, (5) Migrate existing data, and (6) Update Budget Box component.

## Tasks

- [ ] 1. Create database migrations and models
  - [x] 1.1 Create parliament_budgets migration
    - Create migration file with table structure: id, parliament_id (foreign key with cascade delete), year (YEAR type), budget (DECIMAL 15,2), timestamps
    - Add unique constraint on (parliament_id, year)
    - Add index on (parliament_id, year) for query performance
    - _Requirements: 2.1, 2.3, 2.5, 2.7_
  
  - [x] 1.2 Create dun_budgets migration
    - Create migration file with table structure: id, dun_id (foreign key with cascade delete), year (YEAR type), budget (DECIMAL 15,2), timestamps
    - Add unique constraint on (dun_id, year)
    - Add index on (dun_id, year) for query performance
    - _Requirements: 2.2, 2.4, 2.6, 2.7_
  
  - [x] 1.3 Create ParliamentBudget model
    - Define fillable fields: parliament_id, year, budget
    - Add casts for budget (decimal:2) and year (integer)
    - Define belongsTo relationship to Parliament model
    - _Requirements: 2.1, 2.5_
  
  - [x] 1.4 Create DunBudget model
    - Define fillable fields: dun_id, year, budget
    - Add casts for budget (decimal:2) and year (integer)
    - Define belongsTo relationship to Dun model
    - _Requirements: 2.2, 2.6_
  
  - [x] 1.5 Update Parliament model with budget relationships
    - Add hasMany relationship to ParliamentBudget model
    - Implement getBudgetForYear($year) method to retrieve budget for specific year
    - Implement getAllYears() method to get all years with budget entries
    - Remove 'budget' from fillable array
    - _Requirements: 2.1, 3.1_
  
  - [x] 1.6 Update Dun model with budget relationships
    - Add hasMany relationship to DunBudget model
    - Implement getBudgetForYear($year) method to retrieve budget for specific year
    - Implement getAllYears() method to get all years with budget entries
    - Remove 'budget' from fillable array
    - _Requirements: 2.2, 3.2_
  
  - [ ]* 1.7 Write property test for cascade deletion
    - **Property 4: Cascade deletion for Parliament budgets**
    - **Property 5: Cascade deletion for DUN budgets**
    - **Validates: Requirements 2.3, 2.4**

- [ ] 2. Update BudgetCalculationService for year-based calculations
  - [x] 2.1 Update getUserBudgetInfo method signature
    - Add optional $year parameter (default to current year)
    - Update method to call getBudgetForYear($year) instead of accessing budget property
    - Return budget info array with year field included
    - _Requirements: 3.1, 3.2, 5.4_
  
  - [x] 2.2 Update calculateAllocatedBudget method
    - Add $year parameter to method signature
    - Filter pre-projects by project_year matching the specified year
    - Maintain exclusion of Cancelled and Rejected statuses
    - _Requirements: 3.3, 4.5_
  
  - [x] 2.3 Update isWithinBudget method
    - Add $year parameter to method signature
    - Calculate remaining budget for the specified year
    - Compare project cost against year-specific remaining budget
    - _Requirements: 3.6, 4.1_
  
  - [x] 2.4 Update getAvailableBudgetForEdit method
    - Add $year parameter to method signature
    - Exclude current project's cost from allocated budget calculation
    - Calculate remaining budget for the specified year
    - _Requirements: 4.2_
  
  - [ ]* 2.5 Write property tests for budget calculations
    - **Property 6: Parliament total budget calculation**
    - **Property 7: DUN total budget calculation**
    - **Property 8: Allocated budget excludes cancelled and rejected projects**
    - **Property 9: Remaining budget calculation**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 4.5**

- [x] 3. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. Implement dynamic budget entry forms for Parliament
  - [x] 4.1 Update Parliament Blade view with dynamic budget rows
    - Replace single budget input with budget-entries container div
    - Add "+" button to add new year-budget rows
    - Create budget row template with year dropdown (2024-2030) and budget input
    - Add delete button for each row (disabled when only one row exists)
    - Include hidden input fields for form submission (budgets[0][year], budgets[0][budget])
    - _Requirements: 1.1, 1.3, 1.4, 1.5, 7.1, 7.3, 7.4_
  
  - [x] 4.2 Add JavaScript for Parliament budget row management
    - Implement addBudgetRow() function to append new row to container
    - Implement removeBudgetRow(index) function to remove specific row
    - Implement updateDeleteButtons() to disable delete when only one row exists
    - Implement reindexRows() to update array indices after deletion
    - Add event listeners for add and delete buttons
    - Ensure rows are sorted by year after adding/removing
    - _Requirements: 1.3, 1.4, 7.4, 7.5, 7.6, 7.7_
  
  - [x] 4.3 Create Parliament form request validation
    - Create StoreParliamentRequest with validation rules
    - Validate budgets array: required, array, min:1
    - Validate budgets.*.year: required, integer, min:2024, max:2030
    - Validate budgets.*.budget: required, numeric, min:0, max:9999999999999.99
    - Add custom validator to check for duplicate years
    - Add custom error messages for each validation rule
    - _Requirements: 1.6, 1.7, 8.1, 8.3, 8.4, 8.5, 8.6_
  
  - [ ]* 4.4 Write property tests for Parliament form validation
    - **Property 1: Budget amount validation**
    - **Property 2: Parliament year uniqueness**
    - **Property 15: Required field validation**
    - **Validates: Requirements 1.6, 1.7, 2.5, 8.3, 8.4, 8.5, 8.6**

- [ ] 5. Implement dynamic budget entry forms for DUN
  - [x] 5.1 Update DUN Blade view with dynamic budget rows
    - Replace single budget input with budget-entries container div
    - Add "+" button to add new year-budget rows
    - Create budget row template with year dropdown (2024-2030) and budget input
    - Add delete button for each row (disabled when only one row exists)
    - Include hidden input fields for form submission (budgets[0][year], budgets[0][budget])
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 7.2, 7.3, 7.4_
  
  - [x] 5.2 Add JavaScript for DUN budget row management
    - Implement addBudgetRow() function to append new row to container
    - Implement removeBudgetRow(index) function to remove specific row
    - Implement updateDeleteButtons() to disable delete when only one row exists
    - Implement reindexRows() to update array indices after deletion
    - Add event listeners for add and delete buttons
    - Ensure rows are sorted by year after adding/removing
    - _Requirements: 1.3, 1.4, 7.4, 7.5, 7.6, 7.7_
  
  - [x] 5.3 Create DUN form request validation
    - Create StoreDunRequest with validation rules
    - Validate budgets array: required, array, min:1
    - Validate budgets.*.year: required, integer, min:2024, max:2030
    - Validate budgets.*.budget: required, numeric, min:0, max:9999999999999.99
    - Add custom validator to check for duplicate years
    - Add custom error messages for each validation rule
    - _Requirements: 1.6, 1.8, 8.2, 8.3, 8.4, 8.5, 8.6_
  
  - [ ]* 5.4 Write property tests for DUN form validation
    - **Property 1: Budget amount validation**
    - **Property 3: DUN year uniqueness**
    - **Property 15: Required field validation**
    - **Validates: Requirements 1.6, 1.8, 2.6, 8.3, 8.4, 8.5, 8.6**

- [ ] 6. Update Parliament and DUN controllers
  - [x] 6.1 Update Parliament store method
    - Accept budgets array from request
    - Loop through budgets array and create ParliamentBudget records
    - Handle validation errors and preserve form data
    - Return success message with redirect
    - _Requirements: 1.7, 8.7_
  
  - [x] 6.2 Update Parliament update method
    - Delete existing budget entries for the Parliament
    - Create new budget entries from budgets array
    - Handle validation errors and preserve form data
    - Return success message with redirect
    - _Requirements: 1.7, 8.7_
  
  - [x] 6.3 Update DUN store method
    - Accept budgets array from request
    - Loop through budgets array and create DunBudget records
    - Handle validation errors and preserve form data
    - Return success message with redirect
    - _Requirements: 1.8, 8.7_
  
  - [x] 6.4 Update DUN update method
    - Delete existing budget entries for the DUN
    - Create new budget entries from budgets array
    - Handle validation errors and preserve form data
    - Return success message with redirect
    - _Requirements: 1.8, 8.7_

- [x] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Update pre-project budget validation
  - [x] 8.1 Update pre-project form to pass year to budget validation
    - Modify pre-project create form to pass project_year to BudgetCalculationService
    - Display year-specific budget information in Budget Box
    - Show error message if no budget exists for selected year
    - _Requirements: 3.6, 4.4, 5.5_
  
  - [x] 8.2 Update PreProject form request validation
    - Add validation rule to check if budget exists for project_year
    - Call BudgetCalculationService::isWithinBudget with project_year parameter
    - Display detailed error message showing available budget
    - _Requirements: 4.1, 4.3, 4.4_
  
  - [x] 8.3 Update pre-project edit validation
    - Call BudgetCalculationService::getAvailableBudgetForEdit with project_year
    - Exclude current project's cost from budget calculation
    - Validate new total_cost against adjusted remaining budget
    - _Requirements: 4.2_
  
  - [ ]* 8.4 Write property tests for pre-project budget validation
    - **Property 10: Pre-project budget validation uses correct year**
    - **Property 11: Pre-project creation budget validation**
    - **Property 12: Pre-project edit budget validation excludes self**
    - **Validates: Requirements 3.6, 4.1, 4.2, 4.3**

- [x] 9. Update Budget Box component
  - [x] 9.1 Add year parameter to Budget Box component
    - Accept optional year parameter (default to current year)
    - Pass year to BudgetCalculationService::getUserBudgetInfo
    - Display year in component header
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  
  - [x] 9.2 Add no-budget warning to Budget Box
    - Check if has_budget is false in budget info
    - Display warning message when no budget exists for year
    - Style warning with icon and appropriate color
    - _Requirements: 3.5, 5.5_
  
  - [ ]* 9.3 Write property test for Budget Box year filtering
    - **Property 13: Budget Box year filtering**
    - **Validates: Requirements 5.1, 5.2, 5.3**

- [x] 10. Create data migration
  - [x] 10.1 Create migration to transfer existing budget data
    - Create migration file to migrate data from old structure to new
    - Read existing budget values from parliaments.budget column
    - Create parliament_budgets entries for current year with existing budget values
    - Read existing budget values from duns.budget column
    - Create dun_budgets entries for current year with existing budget values
    - Drop budget column from parliaments table
    - Drop budget column from duns table
    - _Requirements: 6.1, 6.2, 6.3, 6.4_
  
  - [x] 10.2 Implement migration rollback
    - Add budget column back to parliaments table (nullable)
    - Add budget column back to duns table (nullable)
    - Copy current year budget values back to budget columns
    - Drop parliament_budgets table
    - Drop dun_budgets table
    - _Requirements: 6.5, 6.6_
  
  - [ ]* 10.3 Write unit tests for migration
    - Test migration with sample Parliament and DUN data
    - Verify budget entries created for current year
    - Verify old budget columns removed
    - Test rollback restores original structure
    - **Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5, 6.6**

- [x] 11. Update all views using budget data
  - [x] 11.1 Update pre-project list view
    - Pass project_year to Budget Box component
    - Display year-specific budget information
    - _Requirements: 5.1, 5.2, 5.3_
  
  - [x] 11.2 Update pre-project create view
    - Pass selected project_year to Budget Box component
    - Update budget display when year changes
    - _Requirements: 5.1, 5.2, 5.3_
  
  - [x] 11.3 Update pre-project edit view
    - Pass project_year to Budget Box component
    - Display year-specific budget information
    - _Requirements: 5.1, 5.2, 5.3_

- [x] 12. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Migration must be tested thoroughly before running in production
- JavaScript should be vanilla JS to avoid framework dependencies
- Year range (2024-2030) can be configured in a config file for easy updates
