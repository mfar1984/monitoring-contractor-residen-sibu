# Requirements Document

## Introduction

The Contractor Analysis Transfer System enables Agency users and Residen users to transfer multiple projects from the Projects page to Contractor Analysis for the contractor selection process. This system implements strict user access control based on agency assignments and requires mandatory documentation for all transfers.

## Glossary

- **Agency_User**: A user account linked to a specific government agency (DID, JKR, JBAB, etc.) through `agency_category_id`
- **Residen_User**: An administrator user account with full system access through `residen_category_id`
- **Contractor_Analysis_Transfer**: A transfer request containing one or more projects submitted for contractor selection analysis
- **Analysis_Pending**: A project status indicating the project is awaiting contractor analysis
- **Transfer_Number**: A unique identifier for each transfer in format CA/YYYY/###
- **Application_Letter**: A mandatory document attachment required for all transfer submissions

## Requirements

### Requirement 1: User Access Control for Transfer Creation

**User Story:** As a system administrator, I want to restrict transfer creation to authorized users only, so that only Agency users and Residen users can initiate contractor analysis transfers.

#### Acceptance Criteria

1. WHEN an Agency_User accesses the contractor analysis transfer page, THE System SHALL display the "Create Transfer" button
2. WHEN a Residen_User accesses the contractor analysis transfer page, THE System SHALL display the "Create Transfer" button
3. WHEN a Parliament user accesses the contractor analysis transfer page, THE System SHALL NOT display the "Create Transfer" button
4. WHEN a DUN user accesses the contractor analysis transfer page, THE System SHALL NOT display the "Create Transfer" button
5. WHEN a Contractor user accesses the contractor analysis transfer page, THE System SHALL NOT display the "Create Transfer" button

### Requirement 2: Agency-Based Data Isolation for Project Selection

**User Story:** As an Agency user, I want to see only projects from my agency, so that I can transfer projects within my organizational scope.

#### Acceptance Criteria

1. WHEN an Agency_User opens the transfer creation form, THE System SHALL display only projects where `agency_category_id` matches the user's `agency_category_id`
2. WHEN a Residen_User opens the transfer creation form, THE System SHALL display all available projects regardless of agency
3. WHEN displaying available projects, THE System SHALL exclude projects that are already in contractor analysis status
4. WHEN displaying available projects, THE System SHALL show project number, project name, total cost, and current status

### Requirement 3: Multiple Project Selection

**User Story:** As an Agency user, I want to select multiple projects in a single transfer, so that I can efficiently submit related projects for contractor analysis together.

#### Acceptance Criteria

1. WHEN the transfer creation form is displayed, THE System SHALL provide checkbox selection for each available project
2. WHEN a user selects projects using checkboxes, THE System SHALL allow selection of multiple projects simultaneously
3. WHEN a user attempts to submit without selecting any project, THE System SHALL prevent submission and display an error message
4. WHEN at least one project is selected, THE System SHALL enable the submit button

### Requirement 4: Mandatory Attachment Upload

**User Story:** As a Residen user, I want to require an application letter for all transfers, so that I have proper documentation for review and approval.

#### Acceptance Criteria

1. WHEN the transfer creation form is displayed, THE System SHALL provide a file upload field labeled "Surat Permohonan" or "Application Letter"
2. WHEN a user attempts to submit without uploading a file, THE System SHALL prevent submission and display an error message
3. WHEN a user uploads a file, THE System SHALL validate that the file format is PDF, DOC, or DOCX
4. WHEN a user uploads a file, THE System SHALL validate that the file size does not exceed 5MB
5. IF a file fails validation, THEN THE System SHALL display a descriptive error message and prevent submission

### Requirement 5: Transfer Submission and Project Status Update

**User Story:** As an Agency user, I want submitted transfers to automatically update project statuses, so that projects are properly tracked in the contractor analysis workflow.

#### Acceptance Criteria

1. WHEN a transfer is successfully submitted, THE System SHALL generate a unique Transfer_Number in format CA/YYYY/###
2. WHEN a transfer is successfully submitted, THE System SHALL store the attachment file in the system storage
3. WHEN a transfer is successfully submitted, THE System SHALL change the status of all selected projects to "Analysis Pending"
4. WHEN a transfer is successfully submitted, THE System SHALL record the `created_by` user ID and `agency_category_id`
5. WHEN a transfer is successfully submitted, THE System SHALL redirect the user to the contractor analysis list page with a success message

### Requirement 6: Contractor Analysis Transfer List Display

**User Story:** As an Agency user, I want to view all my contractor analysis transfers, so that I can track the status of my submissions.

#### Acceptance Criteria

1. WHEN an Agency_User accesses the contractor analysis list page, THE System SHALL display only transfers where `agency_category_id` matches the user's agency
2. WHEN a Residen_User accesses the contractor analysis list page, THE System SHALL display all transfers regardless of agency
3. WHEN displaying the transfer list, THE System SHALL show transfer number, agency name, projects count, status, created date, and action buttons
4. WHEN displaying the transfer list, THE System SHALL use the data-table component for consistent design
5. WHEN displaying the transfer list, THE System SHALL provide search functionality for transfer numbers

### Requirement 7: Transfer Detail Viewing

**User Story:** As an Agency user, I want to view details of a transfer, so that I can review the projects and attachment included in the submission.

#### Acceptance Criteria

1. WHEN a user clicks the "View" action on a transfer, THE System SHALL display the transfer detail page
2. WHEN the transfer detail page is displayed, THE System SHALL show transfer number, agency, created date, status, and creator information
3. WHEN the transfer detail page is displayed, THE System SHALL show a table of all projects included in the transfer
4. WHEN the transfer detail page is displayed, THE System SHALL provide a download link for the uploaded attachment
5. WHEN the transfer detail page is displayed, THE System SHALL show project details including project number, name, total cost, and current status

### Requirement 8: Transfer Deletion for Draft Status

**User Story:** As an Agency user, I want to delete draft transfers, so that I can remove transfers that were created by mistake or are no longer needed.

#### Acceptance Criteria

1. WHEN a transfer has status "Draft", THE System SHALL display a "Delete" action button
2. WHEN a transfer has status other than "Draft", THE System SHALL NOT display a "Delete" action button
3. WHEN a user clicks the "Delete" button, THE System SHALL prompt for confirmation before deletion
4. WHEN a draft transfer is deleted, THE System SHALL remove the transfer record and pivot table entries
5. WHEN a draft transfer is deleted, THE System SHALL delete the attachment file from storage
6. WHEN a draft transfer is deleted, THE System SHALL rollback the status of all included projects to their previous status

### Requirement 9: File Storage and Security

**User Story:** As a system administrator, I want uploaded attachments to be stored securely, so that sensitive documents are protected and accessible only to authorized users.

#### Acceptance Criteria

1. WHEN a file is uploaded, THE System SHALL store the file in a secure storage location
2. WHEN a file is uploaded, THE System SHALL generate a unique filename to prevent conflicts
3. WHEN a file is uploaded, THE System SHALL record the file path in the database
4. WHEN a user downloads an attachment, THE System SHALL verify the user has permission to access the transfer
5. IF a user attempts to download an attachment without permission, THEN THE System SHALL deny access and return an error

### Requirement 10: Form Validation and Error Handling

**User Story:** As an Agency user, I want clear validation messages, so that I can correct errors and successfully submit transfers.

#### Acceptance Criteria

1. WHEN form validation fails, THE System SHALL display error messages above the form
2. WHEN a required field is missing, THE System SHALL highlight the field and display a specific error message
3. WHEN file validation fails, THE System SHALL display the specific reason for failure (format, size, etc.)
4. WHEN agency validation fails for project selection, THE System SHALL prevent submission and display an error message
5. WHEN validation passes, THE System SHALL process the submission and display a success message

### Requirement 11: Design Consistency with Existing Pages

**User Story:** As a user, I want the contractor analysis pages to follow the same design as other pages, so that the interface is familiar and easy to use.

#### Acceptance Criteria

1. WHEN the contractor analysis list page is displayed, THE System SHALL use the data-table component
2. WHEN the transfer creation form is displayed, THE System SHALL use CSS from forms.css for form elements
3. WHEN tables are displayed, THE System SHALL use CSS from table.css for consistent styling
4. WHEN buttons are displayed, THE System SHALL use CSS from buttons.css for consistent styling
5. WHEN the page layout is rendered, THE System SHALL follow the same structure as project transfer and NOC pages

### Requirement 12: Transfer Number Generation

**User Story:** As a system administrator, I want unique transfer numbers generated automatically, so that each transfer can be uniquely identified and tracked.

#### Acceptance Criteria

1. WHEN a transfer is created, THE System SHALL generate a Transfer_Number in format CA/YYYY/###
2. WHEN generating a Transfer_Number, THE System SHALL use the current year for YYYY
3. WHEN generating a Transfer_Number, THE System SHALL use a sequential three-digit number for ###
4. WHEN generating a Transfer_Number, THE System SHALL ensure uniqueness by checking existing transfer numbers
5. WHEN the sequential number exceeds 999, THE System SHALL continue with four or more digits as needed
