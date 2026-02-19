# Requirements Document

## Introduction

The Multi-Year Budget Allocation system transforms the current single-budget model for Parliament and DUN constituencies into a flexible multi-year budget management system. This allows administrators to allocate different budget amounts for different fiscal years, enabling more accurate budget tracking and project planning across multiple years.

## Glossary

- **Parliament**: A parliamentary constituency that receives budget allocations
- **DUN**: A state constituency (Dewan Undangan Negeri) that receives budget allocations
- **Budget_Entry**: A year-budget pair representing allocated funds for a specific fiscal year
- **Pre_Project**: A project proposal that consumes budget from a specific year's allocation
- **Fiscal_Year**: A calendar year for which budget is allocated (e.g., 2024, 2025)
- **Budget_Calculation_Service**: Service that calculates total, allocated, and remaining budgets
- **Budget_Box**: UI component displaying budget information (total, allocated, remaining)

## Requirements

### Requirement 1: Multi-Year Budget Entry Management

**User Story:** As a system administrator, I want to add multiple year-budget entries for each Parliament and DUN, so that I can allocate different budget amounts for different fiscal years.

#### Acceptance Criteria

1. WHEN viewing the Parliament form, THE System SHALL display a dynamic list of year-budget entries with add and delete controls
2. WHEN viewing the DUN form, THE System SHALL display a dynamic list of year-budget entries with add and delete controls
3. WHEN a user clicks the add button, THE System SHALL add a new empty year-budget entry row to the form
4. WHEN a user clicks the delete button on a year-budget entry, THE System SHALL remove that entry from the form
5. WHEN a user selects a year from the dropdown, THE System SHALL display years from 2024 to 2030
6. WHEN a user enters a budget amount, THE System SHALL accept positive decimal values up to 15 digits with 2 decimal places
7. WHEN a user attempts to save duplicate years for the same Parliament, THE System SHALL reject the submission and display an error message
8. WHEN a user attempts to save duplicate years for the same DUN, THE System SHALL reject the submission and display an error message

### Requirement 2: Budget Data Storage

**User Story:** As a system administrator, I want budget entries stored separately from Parliament and DUN records, so that I can maintain multiple years of budget data efficiently.

#### Acceptance Criteria

1. THE System SHALL store Parliament budget entries in a parliament_budgets table with year, budget, and parliament_id columns
2. THE System SHALL store DUN budget entries in a dun_budgets table with year, budget, and dun_id columns
3. WHEN a Parliament is deleted, THE System SHALL cascade delete all associated budget entries
4. WHEN a DUN is deleted, THE System SHALL cascade delete all associated budget entries
5. THE System SHALL enforce unique constraint on year per Parliament in parliament_budgets table
6. THE System SHALL enforce unique constraint on year per DUN in dun_budgets table
7. THE System SHALL store budget amounts as decimal values with 15 digits precision and 2 decimal places

### Requirement 3: Year-Based Budget Calculation

**User Story:** As a system user, I want budget calculations to be based on the selected fiscal year, so that I can see accurate budget information for each year.

#### Acceptance Criteria

1. WHEN calculating total budget for a Parliament, THE Budget_Calculation_Service SHALL sum all budget entries for the specified year
2. WHEN calculating total budget for a DUN, THE Budget_Calculation_Service SHALL sum all budget entries for the specified year
3. WHEN calculating allocated budget, THE Budget_Calculation_Service SHALL sum total_cost of all pre-projects for the specified year excluding Cancelled and Rejected statuses
4. WHEN calculating remaining budget, THE Budget_Calculation_Service SHALL subtract allocated budget from total budget for the specified year
5. WHEN no budget entry exists for a specified year, THE Budget_Calculation_Service SHALL return zero for total budget
6. WHEN validating pre-project budget, THE Budget_Calculation_Service SHALL check against the budget for the pre-project's project_year

### Requirement 4: Pre-Project Year-Based Budget Validation

**User Story:** As a Member of Parliament user, I want pre-project budget validation to check against the specific year's budget, so that I cannot exceed the allocated budget for that year.

#### Acceptance Criteria

1. WHEN creating a pre-project, THE System SHALL validate total_cost against the remaining budget for the selected project_year
2. WHEN editing a pre-project, THE System SHALL validate total_cost against the remaining budget for the project_year excluding the current project's cost
3. WHEN a pre-project would exceed the year's remaining budget, THE System SHALL reject the submission and display an error message showing available budget
4. WHEN a pre-project's project_year has no budget entry, THE System SHALL reject the submission and display an error message indicating no budget allocated for that year
5. THE System SHALL exclude pre-projects with status Cancelled or Rejected from budget allocation calculations

### Requirement 5: Budget Box Year Filtering

**User Story:** As a system user, I want the Budget Box to display budget information for the selected year, so that I can see year-specific budget status.

#### Acceptance Criteria

1. WHEN the Budget_Box component receives a year parameter, THE System SHALL display total budget for that specific year
2. WHEN the Budget_Box component receives a year parameter, THE System SHALL display allocated budget for that specific year
3. WHEN the Budget_Box component receives a year parameter, THE System SHALL display remaining budget for that specific year
4. WHEN no year parameter is provided, THE Budget_Box component SHALL display budget for the current calendar year
5. WHEN the selected year has no budget entry, THE Budget_Box component SHALL display zero for total budget and show a warning message

### Requirement 6: Data Migration and Backward Compatibility

**User Story:** As a system administrator, I want existing single-budget data migrated to the new multi-year system, so that historical data is preserved.

#### Acceptance Criteria

1. WHEN the migration runs, THE System SHALL create budget entries for the current year using existing budget values from parliaments table
2. WHEN the migration runs, THE System SHALL create budget entries for the current year using existing budget values from duns table
3. WHEN the migration completes, THE System SHALL remove the budget column from parliaments table
4. WHEN the migration completes, THE System SHALL remove the budget column from duns table
5. WHEN rolling back the migration, THE System SHALL restore the budget column to parliaments table
6. WHEN rolling back the migration, THE System SHALL restore the budget column to duns table

### Requirement 7: User Interface Dynamic Controls

**User Story:** As a system administrator, I want intuitive add and delete controls for year-budget entries, so that I can easily manage multi-year budgets.

#### Acceptance Criteria

1. WHEN viewing the Parliament form, THE System SHALL display a plus icon button to add new year-budget entries
2. WHEN viewing the DUN form, THE System SHALL display a plus icon button to add new year-budget entries
3. WHEN a year-budget entry row is displayed, THE System SHALL show a delete icon button next to each entry
4. WHEN only one year-budget entry exists, THE System SHALL disable the delete button to prevent removing all entries
5. WHEN a user clicks the add button, THE System SHALL insert a new row immediately below the last entry
6. WHEN a user clicks the delete button, THE System SHALL remove the row with a smooth animation
7. THE System SHALL display year-budget entries in ascending order by year

### Requirement 8: Form Validation and Error Handling

**User Story:** As a system administrator, I want comprehensive validation for year-budget entries, so that I can ensure data integrity.

#### Acceptance Criteria

1. WHEN submitting a Parliament form, THE System SHALL validate that at least one year-budget entry exists
2. WHEN submitting a DUN form, THE System SHALL validate that at least one year-budget entry exists
3. WHEN submitting a form with empty year fields, THE System SHALL reject the submission and highlight the empty fields
4. WHEN submitting a form with empty budget fields, THE System SHALL reject the submission and highlight the empty fields
5. WHEN submitting a form with negative budget amounts, THE System SHALL reject the submission and display an error message
6. WHEN submitting a form with budget amounts exceeding 15 digits, THE System SHALL reject the submission and display an error message
7. WHEN validation fails, THE System SHALL preserve all entered year-budget entries in the form for correction
