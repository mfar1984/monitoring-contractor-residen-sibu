# Requirements Document: Pre-Project Budget Tracking

## Introduction

This feature implements real-time budget tracking for Pre-Projects based on Parliament and DUN budget allocations. The system provides visual budget indicators, real-time calculations, and validation to prevent budget overruns. Each Member of Parliament (YB) can track their allocated budget and see how much remains as they create new pre-projects.

## Glossary

- **Parliament**: A parliamentary constituency with an allocated budget for pre-projects
- **DUN**: A state legislative assembly constituency (Dewan Undangan Negeri) with an allocated budget
- **Pre-Project**: A proposed project that consumes budget allocation from Parliament or DUN
- **Total_Budget**: The total budget allocated to a Parliament or DUN account
- **Allocated_Budget**: The sum of all pre-project costs for a specific Parliament or DUN
- **Remaining_Budget**: The difference between Total Budget and Allocated Budget
- **Budget_Box**: A visual component displaying budget information
- **Budget_Reminder**: A small inline component showing remaining budget during data entry
- **YB**: Yang Berhormat (Member of Parliament) - the user creating pre-projects

## Requirements

### Requirement 1: Budget Data Source

**User Story:** As a system administrator, I want Parliament and DUN accounts to have budget fields, so that budget allocations can be tracked per constituency.

#### Acceptance Criteria

1. THE System SHALL store budget amounts in the parliaments table budget column
2. THE System SHALL store budget amounts in the duns table budget column
3. WHEN a user is logged in, THE System SHALL identify their Parliament or DUN from the users table
4. THE System SHALL use decimal type with precision 15,2 for all budget fields

### Requirement 2: Budget Display Above Pre-Project Table

**User Story:** As a Member of Parliament, I want to see my budget status above the pre-project list, so that I can quickly understand my budget situation before creating projects.

#### Acceptance Criteria

1. WHEN a user views the pre-project list page, THE System SHALL display a Budget Box above the data table
2. THE Budget_Box SHALL display the Total Budget from the user's Parliament or DUN
3. THE Budget_Box SHALL display the Total Allocated amount (sum of all pre-project costs)
4. THE Budget_Box SHALL display the Remaining Budget (Total minus Allocated)
5. WHEN Remaining Budget is positive or zero, THE Budget_Box SHALL use green color scheme
6. WHEN Remaining Budget is negative, THE Budget_Box SHALL use red color scheme
7. THE Budget_Box SHALL use gradient styling consistent with NOC budget boxes
8. THE Budget_Box SHALL format all amounts with RM currency prefix and 2 decimal places

### Requirement 3: Budget Reminder in Create Modal

**User Story:** As a Member of Parliament, I want to see remaining budget while entering project cost, so that I can make informed decisions about project budgets.

#### Acceptance Criteria

1. WHEN a user opens the Create Pre-Project modal, THE System SHALL display a Budget Reminder in the Cost of Project section
2. THE Budget_Reminder SHALL show the current Remaining Budget before any new cost is entered
3. WHEN a user enters or changes the total cost field, THE Budget_Reminder SHALL update in real-time without page refresh
4. THE Budget_Reminder SHALL calculate new remaining budget as: Current Remaining Budget minus Entered Cost
5. WHEN the entered cost exceeds remaining budget, THE Budget_Reminder SHALL display in red color
6. WHEN the entered cost is within remaining budget, THE Budget_Reminder SHALL display in green color
7. THE Budget_Reminder SHALL be compact and non-intrusive in the form layout

### Requirement 4: Budget Validation and Form Control

**User Story:** As a system administrator, I want to prevent budget overruns, so that pre-projects cannot exceed allocated budgets.

#### Acceptance Criteria

1. WHEN a user enters a cost that exceeds remaining budget, THE System SHALL disable the Save button
2. WHEN the Save button is disabled due to budget, THE System SHALL display a clear error message
3. THE Error_Message SHALL state "Budget exceeded. Remaining budget: RM X.XX"
4. WHEN a user reduces the cost to within budget, THE System SHALL re-enable the Save button
5. THE System SHALL prevent form submission when total cost exceeds remaining budget
6. THE System SHALL provide visual indication (grayed out button) when Save is disabled

### Requirement 5: User-Specific Budget Context

**User Story:** As a Member of Parliament, I want to see only my constituency's budget, so that I don't see budget information for other constituencies.

#### Acceptance Criteria

1. WHEN a user has a parliament_id, THE System SHALL use the budget from that Parliament record
2. WHEN a user has a dun_id, THE System SHALL use the budget from that DUN record
3. THE System SHALL calculate Allocated Budget by summing pre-project costs WHERE parliament_id matches user's parliament_id OR dun_id matches user's dun_id
4. THE System SHALL exclude pre-projects with status "Cancelled" from budget calculations
5. THE System SHALL exclude pre-projects with status "Rejected" from budget calculations

### Requirement 6: Real-Time Budget Calculation

**User Story:** As a Member of Parliament, I want budget calculations to update instantly, so that I get immediate feedback on my budget decisions.

#### Acceptance Criteria

1. WHEN a user types in the cost field, THE System SHALL recalculate remaining budget using JavaScript
2. THE System SHALL update the Budget Reminder display within 100 milliseconds of input change
3. THE System SHALL update button state (enabled/disabled) within 100 milliseconds of input change
4. THE System SHALL perform calculations client-side without server requests
5. THE System SHALL validate final budget on server-side before saving

### Requirement 7: Budget Box Styling and Layout

**User Story:** As a user, I want the budget display to be visually clear and consistent, so that I can quickly understand budget status.

#### Acceptance Criteria

1. THE Budget_Box SHALL use a gradient background similar to NOC budget boxes
2. THE Budget_Box SHALL display three rows: Total Budget, Total Allocated, Remaining Budget
3. THE Budget_Box SHALL use bold text for amount values
4. THE Budget_Box SHALL include appropriate spacing and padding for readability
5. THE Budget_Box SHALL be responsive and work on mobile devices
6. WHEN budget is exceeded, THE Budget_Box SHALL use red gradient (from #dc3545 to darker red)
7. WHEN budget is sufficient, THE Budget_Box SHALL use green gradient (from #28a745 to darker green)

### Requirement 8: Edit Pre-Project Budget Validation

**User Story:** As a Member of Parliament, I want budget validation when editing existing pre-projects, so that edits don't cause budget overruns.

#### Acceptance Criteria

1. WHEN a user edits an existing pre-project, THE System SHALL calculate available budget including the original project cost
2. THE System SHALL allow cost increases up to: Remaining Budget plus Original Project Cost
3. WHEN editing, THE Budget_Reminder SHALL show: "Available for this project: RM X.XX"
4. THE System SHALL disable Save button if new cost exceeds available budget for that project
5. THE System SHALL update budget calculations immediately after successful edit
