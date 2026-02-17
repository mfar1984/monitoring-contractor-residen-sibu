# Requirements Document

## Introduction

The Pre-Project Data Completeness feature ensures that Member of Parliament users complete all required data fields before submitting Pre-Projects to EPU for approval. When NOCs are approved, new Pre-Projects are automatically created with minimal data (name, cost, agency) and status "Waiting for EPU Approval". This feature provides visual indicators of data completeness, enforces validation before submission, and manages the status transition from "Waiting for EPU Approval" to "Submitted to EPU".

## Glossary

- **Pre-Project**: A project record in the pre-implementation phase that requires data completion and EPU approval
- **EPU**: Economic Planning Unit - the authority that approves Pre-Projects
- **NOC**: Notice of Change - when approved, automatically creates Pre-Projects
- **Member_of_Parliament**: Users with parliament_id or dun_id who can create and submit Pre-Projects
- **Data_Completeness**: The percentage of required fields that have been filled in a Pre-Project
- **Required_Fields**: Mandatory data fields that must be completed before EPU submission
- **System**: The Laravel Monitoring System application

## Requirements

### Requirement 1: Data Completeness Visual Indicator

**User Story:** As a Member of Parliament user, I want to see the data completeness status of Pre-Projects at a glance, so that I know which projects need more information before submission.

#### Acceptance Criteria

1. THE System SHALL display a "Completeness" column in the Pre-Project list table
2. WHEN calculating completeness, THE System SHALL compute the percentage of required fields filled versus total required fields
3. WHEN completeness is between 0% and 50%, THE System SHALL display the indicator in red color (#dc3545)
4. WHEN completeness is between 51% and 80%, THE System SHALL display the indicator in yellow color (#ffc107)
5. WHEN completeness is between 81% and 100%, THE System SHALL display the indicator in green color (#28a745)
6. THE System SHALL display completeness as a percentage value (e.g., "65%")
7. WHERE a Pre-Project has status "Waiting for EPU Approval", THE System SHALL show the completeness indicator

### Requirement 2: Required Fields Definition

**User Story:** As a system administrator, I want the system to validate specific required fields, so that Pre-Projects contain all necessary information before EPU submission.

#### Acceptance Criteria

1. THE System SHALL define the following fields as required for EPU submission: Project Scope, Project Category, Implementation Period, Division, District, Land Title Status, Implementing Agency, Implementation Method, Project Ownership
2. WHEN calculating completeness percentage, THE System SHALL count only the required fields defined in criterion 2.1
3. THE System SHALL treat a field as filled when it contains a non-null, non-empty value
4. THE System SHALL treat foreign key fields as filled when they reference a valid record in the related table

### Requirement 3: Submit to EPU Button Display

**User Story:** As a Member of Parliament user, I want to see a Submit to EPU button for incomplete Pre-Projects, so that I can submit them once data is complete.

#### Acceptance Criteria

1. WHEN a Pre-Project has status "Waiting for EPU Approval", THE System SHALL display a "Submit to EPU" button in the actions column
2. WHEN a Pre-Project has status other than "Waiting for EPU Approval", THE System SHALL NOT display the "Submit to EPU" button
3. WHEN the logged-in user has parliament_id or dun_id, THE System SHALL display the "Submit to EPU" button for their Pre-Projects
4. WHEN the logged-in user does not have parliament_id or dun_id, THE System SHALL NOT display the "Submit to EPU" button
5. WHEN a Pre-Project has status "Waiting for EPU Approval", THE System SHALL hide the Edit and Delete buttons and show only the Submit button

### Requirement 4: Data Validation Before Submission

**User Story:** As a Member of Parliament user, I want the system to validate data completeness when I submit to EPU, so that I don't submit incomplete projects.

#### Acceptance Criteria

1. WHEN a user clicks "Submit to EPU", THE System SHALL validate that all required fields are filled
2. IF any required fields are missing, THEN THE System SHALL display a modal showing the list of missing fields
3. IF any required fields are missing, THEN THE System SHALL prevent the status change and keep the Pre-Project in "Waiting for EPU Approval" status
4. WHEN all required fields are filled and user confirms submission, THE System SHALL change the status to "Submitted to EPU"
5. WHEN the status changes to "Submitted to EPU", THE System SHALL display a success message to the user
6. WHEN the status changes to "Submitted to EPU", THE System SHALL record the submission timestamp

### Requirement 5: Status Transition and Access Control

**User Story:** As a system administrator, I want to enforce proper status transitions and access control, so that only authorized users can perform specific actions at each stage.

#### Acceptance Criteria

1. WHEN a Pre-Project is created from approved NOC, THE System SHALL set initial status to "Waiting for EPU Approval"
2. WHEN a Pre-Project has status "Waiting for EPU Approval", THE Member_of_Parliament SHALL be able to edit the Pre-Project
3. WHEN a Pre-Project status changes to "Submitted to EPU", THE Member_of_Parliament SHALL NOT be able to edit the Pre-Project
4. WHEN a Pre-Project has status "Submitted to EPU", THE System SHALL allow only EPU approvers to approve or reject it
5. THE System SHALL maintain an audit trail of status changes including user ID and timestamp

### Requirement 6: Missing Fields Modal Display

**User Story:** As a Member of Parliament user, I want to see a clear list of missing required fields, so that I know exactly what information I need to provide.

#### Acceptance Criteria

1. WHEN validation fails due to missing fields, THE System SHALL display a modal dialog
2. THE System SHALL display the modal title as "Incomplete Data"
3. THE System SHALL list all missing required fields by their display names in the modal
4. THE System SHALL provide a "Close" button to dismiss the modal
5. WHEN the user closes the modal, THE System SHALL return to the Pre-Project list without changing the status
6. THE System SHALL use consistent modal styling matching existing delete confirmation modals

### Requirement 7: Completeness Calculation Method

**User Story:** As a developer, I want a reusable method to calculate data completeness, so that the calculation is consistent across the application.

#### Acceptance Criteria

1. THE PreProject model SHALL provide a method named getCompletenessPercentage()
2. THE getCompletenessPercentage() method SHALL return an integer value between 0 and 100
3. THE PreProject model SHALL provide a method named getMissingRequiredFields()
4. THE getMissingRequiredFields() method SHALL return an array of missing field display names
5. WHEN a required field is filled, THE System SHALL include it in the completeness calculation
6. WHEN a required field is empty or null, THE System SHALL exclude it from the completeness calculation and include it in the missing fields array
