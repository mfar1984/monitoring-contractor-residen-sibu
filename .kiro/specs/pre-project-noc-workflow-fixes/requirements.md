# Requirements Document

## Introduction

This specification documents critical workflow corrections for the Pre-Project approval process and NOC (Notice of Change) budget validation. These corrections address inconsistencies between Pre-Project and Project approval workflows, and ensure proper budget allocation validation before NOC creation.

## Glossary

- **Pre-Project**: A project record in the pre-implementation phase that requires approval before EPU submission
- **Project**: A fully approved project that has been transferred from Pre-Project status
- **NOC**: Notice of Change - document that tracks budget changes across multiple projects
- **EPU**: Economic Planning Unit - the authority that approves Pre-Projects
- **Approver**: User authorized to approve Pre-Projects (configured in Approver settings)
- **Budget Allocation**: The process of assigning new costs to projects within a NOC
- **Empty Row**: A row in the NOC project table that has no New Cost value entered
- **System**: The Laravel Monitoring System application

## Requirements

### Requirement 1: Pre-Project Single Approval Level

**User Story:** As a system administrator, I want Pre-Projects to have only one approval level (not two), so that the approval workflow is distinct from the Project approval workflow.

#### Acceptance Criteria

1.1 WHEN a Pre-Project has status "Waiting for Approver 1", THE System SHALL allow any user in the Pre-Project Approvers list to approve it

1.2 WHEN a Pre-Project Approver approves a Pre-Project, THE System SHALL change the status directly to "Waiting for EPU Approval" (skipping "Waiting for Approver 2")

1.3 THE System SHALL NOT display "Waiting for Approver 2" status for Pre-Projects

1.4 WHEN displaying the approval modal for Pre-Projects, THE System SHALL show the text "Status will change to 'Waiting for EPU Approval'"

1.5 THE System SHALL NOT change the approval modal text based on current status for Pre-Projects

1.6 WHEN a Pre-Project Approver rejects a Pre-Project, THE System SHALL only check for "Waiting for Approver 1" status (not "Waiting for Approver 2")

### Requirement 2: Project Two-Level Approval (Unchanged)

**User Story:** As a system administrator, I want Projects to maintain their two-level approval workflow, so that Projects have stricter approval controls than Pre-Projects.

#### Acceptance Criteria

2.1 WHEN a Project has status "Waiting for Approval 1", THE System SHALL allow only the designated Approver 1 to approve it

2.2 WHEN Approver 1 approves a Project, THE System SHALL change the status to "Waiting for Approval 2"

2.3 WHEN a Project has status "Waiting for Approval 2", THE System SHALL allow only the designated Approver 2 to approve it

2.4 WHEN Approver 2 approves a Project, THE System SHALL change the status to "Approved"

2.5 THE System SHALL maintain separate approval workflows for Pre-Projects (1 level) and Projects (2 levels)

### Requirement 3: NOC Budget Full Allocation Requirement

**User Story:** As a Member of Parliament user, I want the system to prevent NOC creation until all budget is fully allocated, so that no budget is left unassigned.

#### Acceptance Criteria

3.1 WHEN the Remaining Budget is greater than RM 0.00, THE System SHALL disable the "Create NOC" button

3.2 WHEN the Remaining Budget is greater than RM 0.00, THE System SHALL display a yellow info message "Budget Not Fully Allocated"

3.3 WHEN the Remaining Budget equals RM 0.00 AND there are no empty rows, THE System SHALL enable the "Create NOC" button

3.4 THE System SHALL calculate Remaining Budget as: Total NOC Budget minus Total Allocated Budget

3.5 THE System SHALL update the Remaining Budget calculation in real-time as users enter New Cost values

### Requirement 4: NOC Empty Row Detection

**User Story:** As a Member of Parliament user, I want the system to prevent NOC creation when there are empty rows, so that all project changes are properly documented.

#### Acceptance Criteria

4.1 THE System SHALL define an empty row as a row in the projects table that has no New Cost value entered (value is 0, null, or empty string)

4.2 WHEN there are empty rows in the projects table, THE System SHALL disable the "Create NOC" button

4.3 WHEN there are empty rows in the projects table, THE System SHALL display a red warning message "Empty Rows Detected"

4.4 THE System SHALL provide guidance text "Please delete empty rows (rows without New Cost) before creating NOC"

4.5 WHEN all empty rows are deleted, THE System SHALL re-enable validation checks for budget allocation

4.6 THE System SHALL check for empty rows in real-time as users add or remove rows

4.7 THE System SHALL ignore the empty state row (row with "No projects added yet" message) when checking for empty rows

4.8 THE System SHALL only validate rows that contain actual project data (rows with .kos-baru-input field)

### Requirement 5: NOC Over Budget Prevention

**User Story:** As a Member of Parliament user, I want the system to prevent NOC creation when budget is exceeded, so that budget constraints are enforced.

#### Acceptance Criteria

5.1 WHEN the Remaining Budget is less than RM 0.00 (negative), THE System SHALL disable the "Create NOC" button

5.2 WHEN the Remaining Budget is less than RM 0.00, THE System SHALL display a red error message "Budget Exceeded"

5.3 WHEN the Remaining Budget is less than RM 0.00, THE System SHALL display the Remaining Budget value in red color

5.4 THE System SHALL prioritize the over budget error message above other validation messages

5.5 WHEN the user reduces allocated costs to bring Remaining Budget to RM 0.00 or positive, THE System SHALL remove the over budget error

### Requirement 6: NOC Validation Message Priority

**User Story:** As a Member of Parliament user, I want to see the most critical validation message first, so that I know which issue to fix first.

#### Acceptance Criteria

6.1 THE System SHALL display validation messages in the following priority order:
   1. Budget Exceeded (red error) - highest priority
   2. Empty Rows Detected (red warning) - medium priority
   3. Budget Not Fully Allocated (yellow info) - lowest priority

6.2 THE System SHALL display only one validation message at a time (the highest priority issue)

6.3 WHEN multiple validation issues exist, THE System SHALL hide lower priority messages until higher priority issues are resolved

6.4 WHEN all validation issues are resolved, THE System SHALL hide all validation messages

6.5 THE System SHALL use consistent styling for validation messages:
   - Red border-left for errors and warnings
   - Yellow border-left for info messages
   - Material Symbols icons (error, warning, info)

### Requirement 7: NOC Budget Summary Display

**User Story:** As a Member of Parliament user, I want to see a clear budget summary, so that I understand the current budget allocation status.

#### Acceptance Criteria

7.1 THE System SHALL display a budget summary box with the following information:
   - Total NOC Budget (sum of all imported project costs)
   - Total Allocated Budget (sum of all New Cost values)
   - Remaining Budget (Total NOC Budget minus Total Allocated Budget)

7.2 THE System SHALL display the Remaining Budget in black color when >= RM 0.00

7.3 THE System SHALL display the Remaining Budget in red color when < RM 0.00

7.4 THE System SHALL update all budget summary values in real-time as users enter New Cost values

7.5 THE System SHALL format all currency values with "RM" prefix and two decimal places

### Requirement 8: Pre-Project Approval Modal Consistency

**User Story:** As a Pre-Project Approver, I want the approval modal to show consistent and accurate information, so that I understand the impact of my approval action.

#### Acceptance Criteria

8.1 THE System SHALL display a static approval modal for Pre-Projects that does not change based on current status

8.2 THE System SHALL display the following information in the Pre-Project approval modal:
   - "Are you sure you want to approve this Pre-Project?"
   - "Status will change to 'Waiting for EPU Approval'"
   - Pre-Project name
   - Remarks input field

8.3 THE System SHALL NOT include JavaScript logic to change modal text based on Pre-Project status

8.4 THE System SHALL use the same modal styling as other confirmation modals in the application

8.5 WHEN the approver confirms approval, THE System SHALL update the status to "Waiting for EPU Approval" and record the approval timestamp and user ID

### Requirement 9: Approval Workflow Status Display

**User Story:** As a user, I want to see only valid status badges for Pre-Projects, so that I'm not confused by statuses that don't apply.

#### Acceptance Criteria

9.1 THE System SHALL display the following status badges for Pre-Projects:
   - "Waiting for Complete Form" (grey)
   - "Waiting for Approver 1" (yellow)
   - "Waiting for EPU Approval" (blue)
   - "Approved" (green)
   - "Rejected" (red)

9.2 THE System SHALL NOT display "Waiting for Approver 2" status badge for Pre-Projects

9.3 THE System SHALL display action buttons based on Pre-Project status:
   - "Waiting for Approver 1": Show Approve/Reject buttons (for approvers only)
   - "Waiting for EPU Approval": Show Submit to EPU button (for Parliament/DUN users)
   - Other statuses: Show View/Print buttons only

9.4 THE System SHALL hide Edit and Delete buttons when Pre-Project status is "Waiting for Approver 1" or later

### Requirement 10: Completeness Percentage Display Rules

**User Story:** As a user, I want to see completeness percentage only when relevant, so that the display is not cluttered with unnecessary information.

#### Acceptance Criteria

10.1 THE System SHALL display completeness percentage only for Pre-Projects with status "Waiting for Complete Form" or "Waiting for EPU Approval"

10.2 THE System SHALL NOT display completeness percentage for Pre-Projects with status "Waiting for Approver 1", "Approved", or "Rejected"

10.3 WHEN completeness percentage is not displayed, THE System SHALL show "N/A" in the Completeness column

10.4 THE System SHALL use color-coded badges for completeness percentage:
   - Red (#dc3545) for 0-50%
   - Yellow (#ffc107) for 51-80%
   - Green (#28a745) for 81-100%

