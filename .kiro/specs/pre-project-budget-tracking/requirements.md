# Requirements Document: Pre-Project Budget Tracking

## Introduction

The Pre-Project Budget Tracking system enables Parliament and DUN users to monitor their budget allocation and spending in real-time. The system displays budget information on the pre-project list page, provides budget reminders during pre-project creation/editing, validates budget constraints to prevent overspending, and offers Residen/Admin users an aggregated budget overview across all Parliaments and DUNs.

## Glossary

- **Parliament**: A parliamentary constituency with assigned budget allocation
- **DUN**: A state constituency (Dewan Undangan Negeri) with assigned budget allocation
- **Pre-Project**: A project proposal created by Parliament/DUN users that consumes budget
- **Total Budget**: The total budget allocated to a Parliament or DUN for a specific year
- **Total Allocated**: The sum of all pre-project costs with status "Waiting for Approval" or "Approved"
- **Remaining Budget**: The difference between Total Budget and Total Allocated
- **Residen**: Administrator user category with oversight of all Parliaments and DUNs
- **Budget Year**: The fiscal year for which budget is allocated and tracked
- **Budget_System**: The budget tracking and validation system

## Requirements

### Requirement 1: Budget Display on Pre-Project List Page

**User Story:** As a Parliament/DUN user, I want to see my budget information at the top of the pre-project list page, so that I can track my spending and remaining budget.

#### Acceptance Criteria

1. WHEN a Parliament or DUN user visits the pre-project list page, THE Budget_System SHALL display three budget boxes above the pre-project table
2. THE Budget_System SHALL display the Total Budget box showing the total budget allocated to the user's Parliament or DUN for the current year
3. THE Budget_System SHALL display the Total Allocated box showing the sum of all pre-project costs with status "Waiting for Approval" or "Approved"
4. THE Budget_System SHALL display the Remaining Budget box showing Total Budget minus Total Allocated
5. WHEN the Remaining Budget is negative, THE Budget_System SHALL display the Remaining Budget box with red styling
6. THE Budget_System SHALL display the budget year label in the format "Budget for Year YYYY"
7. THE Budget_System SHALL format all currency values in the format "RM X,XXX,XXX.XX"
8. THE Budget_System SHALL use gradient colors for the budget boxes (blue for Total Budget, green for Total Allocated, yellow or red for Remaining Budget)

### Requirement 2: Budget Reminder in Create/Edit Modal

**User Story:** As a Parliament/DUN user, I want to see a budget reminder when creating or editing pre-projects, so that I know if I'm exceeding my budget before saving.

#### Acceptance Criteria

1. WHEN a Parliament or DUN user opens the Create Pre-Project modal, THE Budget_System SHALL display a budget reminder box in the Cost of Project section
2. WHEN a Parliament or DUN user opens the Edit Pre-Project modal, THE Budget_System SHALL display a budget reminder box in the Cost of Project section
3. THE Budget_System SHALL display the current Remaining Budget in the format "Remaining Budget: RM X,XXX,XXX.XX"
4. WHEN the user enters or modifies cost values, THE Budget_System SHALL update the budget reminder in real-time
5. WHEN the entered cost would cause the budget to be exceeded, THE Budget_System SHALL change the budget reminder box color to red
6. WHEN the entered cost would not exceed the budget, THE Budget_System SHALL display the budget reminder box in green
7. THE Budget_System SHALL position the budget reminder box below the cost input fields

### Requirement 3: Budget Validation on Save

**User Story:** As the system, I want to prevent Parliament/DUN users from creating or saving pre-projects that exceed their budget, so that budget constraints are enforced.

#### Acceptance Criteria

1. WHEN a Parliament or DUN user attempts to save a pre-project, THE Budget_System SHALL calculate the new Total Allocated as Current Total Allocated plus the pre-project total cost
2. IF the new Total Allocated exceeds the Total Budget, THEN THE Budget_System SHALL disable the Save or Create Pre-Project button
3. WHEN the budget is exceeded, THE Budget_System SHALL display a warning message "Budget exceeded! You cannot create this pre-project as it exceeds your remaining budget."
4. WHEN the budget is exceeded, THE Budget_System SHALL grey out the Save or Create Pre-Project button and make it unclickable
5. WHEN a Residen or Admin user creates or edits a pre-project, THE Budget_System SHALL not apply budget validation
6. WHEN the user reduces the cost to within budget limits, THE Budget_System SHALL re-enable the Save or Create Pre-Project button
7. THE Budget_System SHALL validate against the total_cost field of the pre-project

### Requirement 4: Residen Dashboard Budget Overview

**User Story:** As a Residen/Admin user, I want to see a total budget overview from all Parliaments and DUNs, so that I can monitor overall budget utilization across the system.

#### Acceptance Criteria

1. WHEN a Residen user visits the Residen page, THE Budget_System SHALL display four budget overview boxes at the top of the page
2. THE Budget_System SHALL display the Total Parliament Budget box showing the sum of all Parliament budgets for the current year
3. THE Budget_System SHALL display the Total DUN Budget box showing the sum of all DUN budgets for the current year
4. THE Budget_System SHALL display the Total Allocated box showing the sum of all pre-project costs from all Parliaments and DUNs with status "Waiting for Approval" or "Approved"
5. THE Budget_System SHALL display the Overall Remaining box showing (Total Parliament Budget plus Total DUN Budget) minus Total Allocated
6. THE Budget_System SHALL use the same visual styling as pre-project budget boxes with gradient colors
7. THE Budget_System SHALL display a year selector dropdown to allow viewing budget data for different years
8. WHEN the Residen user selects a different year, THE Budget_System SHALL update all budget overview boxes to show data for the selected year

### Requirement 5: Budget Calculation Logic

**User Story:** As the system, I want to calculate budgets accurately based on user roles and data relationships, so that budget information is reliable and correct.

#### Acceptance Criteria

1. WHEN calculating budget for a Parliament user, THE Budget_System SHALL retrieve the budget from the parliaments table where parliament_id matches the user's parliament_category_id
2. WHEN calculating budget for a DUN user, THE Budget_System SHALL retrieve the budget from the duns table where dun_id matches the user's parliament_category_id
3. WHEN calculating Total Allocated for a Parliament user, THE Budget_System SHALL sum the total_cost of all pre-projects where parliament_id matches the user's parliament and status is "Waiting for Approval" or "Approved"
4. WHEN calculating Total Allocated for a DUN user, THE Budget_System SHALL sum the total_cost of all pre-projects where dun_id matches the user's DUN and status is "Waiting for Approval" or "Approved"
5. WHEN calculating Remaining Budget, THE Budget_System SHALL subtract Total Allocated from Total Budget
6. WHEN calculating Total Parliament Budget for Residen users, THE Budget_System SHALL sum all budget values from the parliaments table
7. WHEN calculating Total DUN Budget for Residen users, THE Budget_System SHALL sum all budget values from the duns table
8. WHEN calculating Total Allocated for Residen users, THE Budget_System SHALL sum the total_cost of all pre-projects with status "Waiting for Approval" or "Approved" across all Parliaments and DUNs

### Requirement 6: User Access Control

**User Story:** As the system, I want to display budget information only to authorized users based on their role, so that users see relevant budget data for their scope.

#### Acceptance Criteria

1. WHEN a user with parliament_category_id visits the pre-project list page, THE Budget_System SHALL display budget boxes filtered to their Parliament
2. WHEN a user with parliament_category_id and DUN type visits the pre-project list page, THE Budget_System SHALL display budget boxes filtered to their DUN
3. WHEN a user with residen_category_id visits the Residen page, THE Budget_System SHALL display aggregated budget overview boxes
4. WHEN a user without parliament_category_id or residen_category_id visits pages with budget information, THE Budget_System SHALL not display budget boxes
5. THE Budget_System SHALL apply budget validation only to users with parliament_category_id (Parliament or DUN users)
6. THE Budget_System SHALL not apply budget validation to users with residen_category_id (Residen or Admin users)

### Requirement 7: Multi-Year Budget Support

**User Story:** As a Parliament/DUN user, I want to see budget information for the current year by default, so that I'm tracking the most relevant budget period.

#### Acceptance Criteria

1. THE Budget_System SHALL display budget information for the current calendar year by default
2. THE Budget_System SHALL retrieve budget data from the parliaments.budget or duns.budget columns for the current year
3. WHEN multi-year budget tables are implemented, THE Budget_System SHALL retrieve budget data from the appropriate year-specific tables
4. THE Budget_System SHALL calculate Total Allocated based on pre-projects created within the current budget year
5. WHEN displaying the budget year label, THE Budget_System SHALL show the current year in the format "Budget for Year YYYY"
