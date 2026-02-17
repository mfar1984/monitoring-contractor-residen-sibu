# Requirements Document

## Introduction

The NOC (Notice of Change) system is currently incorrectly placed under the Pre-Project module at `/pages/pre-project/noc`. This restructuring moves the NOC system to its correct location under the Project module at `/pages/project`, implements a proper project transfer workflow from approved pre-projects, and creates a three-tab Project page structure. This change aligns the system with the correct business workflow: Pre-Project → Approval → Project → NOC.

## Glossary

- **Pre-Project**: Initial project proposal awaiting approval
- **Project**: Approved pre-project that has been transferred and assigned a project number
- **NOC**: Notice of Change - document for modifying approved projects
- **Project_Transfer**: Process of converting approved pre-project to project
- **Project_Number**: Unique identifier assigned to projects after transfer
- **NOC_System**: Complete NOC functionality including routes, controllers, views, and database tables
- **Pivot_Table**: Junction table connecting NOCs to projects (noc_project)
- **Parliament_User**: User with parliament_category_id assignment
- **DUN_User**: User with dun_id assignment
- **Approval_Workflow**: Two-level approval process (First Approver → Second Approver)

## Requirements

### Requirement 1: Projects Table and Data Model

**User Story:** As a system administrator, I want a dedicated projects table to store approved pre-projects, so that approved projects are properly separated from pending pre-projects.

#### Acceptance Criteria

1. THE System SHALL create a projects table with all fields from pre_projects table
2. THE projects table SHALL include a project_number field as unique identifier
3. THE projects table SHALL include a pre_project_id field referencing the original pre-project
4. THE projects table SHALL include an approval_date field storing when pre-project was approved
5. THE projects table SHALL include a transferred_at timestamp field
6. THE System SHALL maintain all existing pre_projects table fields in projects table
7. THE System SHALL establish a foreign key relationship between projects.pre_project_id and pre_projects.id

### Requirement 2: Project Transfer Mechanism

**User Story:** As a system administrator, I want approved pre-projects to automatically transfer to the projects table, so that the workflow correctly reflects the business process.

#### Acceptance Criteria

1. WHEN a pre-project status changes to "Approved" THEN THE System SHALL create a corresponding project record
2. WHEN creating a project record THEN THE System SHALL generate a unique project_number
3. WHEN creating a project record THEN THE System SHALL copy all pre-project data to the project
4. WHEN creating a project record THEN THE System SHALL set pre_project_id to reference the original pre-project
5. WHEN creating a project record THEN THE System SHALL set approval_date to the current timestamp
6. WHEN creating a project record THEN THE System SHALL set transferred_at to the current timestamp
7. THE project_number format SHALL follow pattern "PROJ/YYYY/###" where YYYY is year and ### is sequential number

### Requirement 3: NOC Database Restructuring

**User Story:** As a developer, I want NOC tables to reference projects instead of pre-projects, so that NOCs correctly operate on approved projects.

#### Acceptance Criteria

1. THE System SHALL rename noc_pre_project pivot table to noc_project
2. THE noc_project table SHALL replace pre_project_id column with project_id column
3. THE noc_project.project_id SHALL reference projects.id as foreign key
4. THE System SHALL preserve all existing noc_project columns except the ID reference change
5. THE System SHALL maintain all existing nocs table structure without changes
6. THE System SHALL maintain all existing noc_notes table structure without changes
7. WHEN migrating data THEN THE System SHALL map existing noc_pre_project records to corresponding projects

### Requirement 4: NOC Model Relationships Update

**User Story:** As a developer, I want NOC model relationships to use projects instead of pre-projects, so that the code correctly reflects the new data structure.

#### Acceptance Criteria

1. THE Noc model SHALL define a belongsToMany relationship with Project model
2. THE Noc model SHALL use noc_project as the pivot table name
3. THE Noc model SHALL remove the preProjects relationship method
4. THE Noc model SHALL add a projects relationship method
5. THE Project model SHALL define a belongsToMany relationship with Noc model
6. THE pivot relationship SHALL include all custom fields from noc_project table
7. THE System SHALL update all Noc model methods that reference preProjects to use projects

### Requirement 5: NOC Routes Migration

**User Story:** As a developer, I want all NOC routes moved from pre-project to project namespace, so that URLs correctly reflect the NOC location.

#### Acceptance Criteria

1. THE System SHALL change route prefix from /pages/pre-project/noc to /pages/project/noc
2. THE System SHALL update route GET /pages/project/noc for NOC list page
3. THE System SHALL update route GET /pages/project/noc/create for NOC create page
4. THE System SHALL update route POST /pages/project/noc for NOC store action
5. THE System SHALL update route GET /pages/project/noc/{id} for NOC detail page
6. THE System SHALL update route POST /pages/project/noc/{id}/submit for NOC submission
7. THE System SHALL update route POST /pages/project/noc/{id}/approve for NOC approval
8. THE System SHALL update route POST /pages/project/noc/{id}/reject for NOC rejection
9. THE System SHALL update route GET /pages/project/noc/{id}/print for NOC print view
10. THE System SHALL update route DELETE /pages/project/noc/{id} for NOC deletion
11. THE System SHALL maintain all existing route parameters and middleware

### Requirement 6: NOC Controller Methods Migration

**User Story:** As a developer, I want NOC controller methods renamed to reflect project namespace, so that code is consistent with the new structure.

#### Acceptance Criteria

1. THE System SHALL rename preProjectNoc method to projectNoc
2. THE System SHALL rename preProjectNocCreate method to projectNocCreate
3. THE System SHALL rename preProjectNocStore method to projectNocStore
4. THE System SHALL rename preProjectNocShow method to projectNocShow
5. THE System SHALL rename preProjectNocSubmit method to projectNocSubmit
6. THE System SHALL rename preProjectNocApprove method to projectNocApprove
7. THE System SHALL rename preProjectNocReject method to projectNocReject
8. THE System SHALL rename preProjectNocPrint method to projectNocPrint
9. THE System SHALL rename preProjectNocDelete method to projectNocDelete
10. THE System SHALL update all method implementations to query projects table instead of pre_projects
11. THE System SHALL update all method implementations to use Project model instead of PreProject model

### Requirement 7: NOC Views Migration

**User Story:** As a developer, I want NOC view files renamed and updated to use project routes, so that the frontend correctly reflects the new structure.

#### Acceptance Criteria

1. THE System SHALL rename pre-project-noc.blade.php to project-noc.blade.php
2. THE System SHALL rename pre-project-noc-create.blade.php to project-noc-create.blade.php
3. THE System SHALL rename pre-project-noc-show.blade.php to project-noc-show.blade.php
4. THE System SHALL rename pre-project-noc-print.blade.php to project-noc-print.blade.php
5. THE System SHALL update all route references in views from pages.pre-project.noc.* to pages.project.noc.*
6. THE System SHALL update all form actions to use new project-based routes
7. THE System SHALL update all redirect URLs to use new project-based routes
8. THE System SHALL preserve all existing view functionality and styling

### Requirement 8: Project Page with Three Tabs

**User Story:** As a user, I want a Project page with three tabs (Project, NOC, Project Cancel), so that I can access all project-related functions in one place.

#### Acceptance Criteria

1. THE System SHALL create a project page at route /pages/project
2. THE Project page SHALL display three tabs: "Project", "NOC", "Project Cancel"
3. WHEN "Project" tab is active THEN THE System SHALL display the list of approved projects
4. WHEN "NOC" tab is active THEN THE System SHALL display the NOC list page
5. WHEN "Project Cancel" tab is active THEN THE System SHALL display a placeholder message
6. THE tab component SHALL use the same design pattern as master-data-tabs component
7. THE System SHALL highlight the active tab based on current route
8. THE tab navigation SHALL support horizontal drag scrolling on mobile devices

### Requirement 9: Project List Display

**User Story:** As a user, I want to view all approved projects in the Project tab, so that I can see which pre-projects have been transferred to projects.

#### Acceptance Criteria

1. THE Project list SHALL use the data-table component for consistent design
2. THE Project list SHALL display columns: Project Number, Project Name, Parliament/DUN, Total Cost, Approval Date, Actions
3. THE Project list SHALL filter projects based on user's parliament_id or dun_id
4. THE Project list SHALL support search functionality across project fields
5. THE Project list SHALL support pagination with configurable rows per page
6. THE Project list SHALL display View action button for each project
7. THE System SHALL auto-detect Parliament/DUN from logged-in user without manual selection

### Requirement 10: NOC Import Projects Update

**User Story:** As a NOC creator, I want to import from the projects table instead of pre-projects, so that I can only create NOCs for approved projects.

#### Acceptance Criteria

1. WHEN opening NOC create page THEN THE System SHALL load projects from projects table
2. THE import modal SHALL display only projects matching user's parliament_id or dun_id
3. THE import modal SHALL exclude projects already included in existing NOCs
4. THE import modal SHALL display Project Number, Project Name, and Total Cost
5. WHEN importing projects THEN THE System SHALL populate project data from projects table
6. THE System SHALL maintain all existing NOC create functionality with projects instead of pre-projects

### Requirement 17: NOC Project Changes to Pre-Project Integration

**User Story:** As a NOC creator, I want imported projects with changes to be sent back to Pre-Project for EPU approval, so that project modifications follow the proper approval workflow.

#### Acceptance Criteria

1. WHEN importing a project with existing Project Number THEN THE System SHALL keep the Project Number in the NOC
2. WHEN a NOC is submitted with imported projects that have changes THEN THE System SHALL create new pre-project records for each changed project
3. THE new pre-project records SHALL have status "Waiting For EPU Approval"
4. THE new pre-project records SHALL include the Project Number from the original project
5. THE new pre-project records SHALL include the new project name if changed, otherwise original name
6. THE new pre-project records SHALL include the new cost if changed, otherwise original cost
7. THE new pre-project records SHALL include the new implementing agency if changed, otherwise original agency
8. THE new pre-project records SHALL include all other fields from the original project
9. THE new pre-project records SHALL be visible in the Pre-Project list at /pages/pre-project
10. THE Pre-Project list SHALL display these records with status "Waiting For EPU Approval"
11. THE Pre-Project list SHALL keep the Project Number field populated for these records
12. WHEN a project is added via "Add New" button (no Project Number) THEN THE System SHALL NOT create a pre-project record
13. THE System SHALL distinguish between imported projects (with Project Number) and new projects (without Project Number)

### Requirement 11: Sidebar Navigation Update

**User Story:** As a user, I want the sidebar to show Project menu instead of NOC under Pre-Project, so that navigation reflects the correct structure.

#### Acceptance Criteria

1. THE sidebar SHALL add a "Project" menu item at the same level as "Pre-Project"
2. THE "Project" menu item SHALL link to /pages/project route
3. THE sidebar SHALL remove "NOC" submenu from under "Pre-Project"
4. THE sidebar SHALL maintain all other existing menu items unchanged
5. THE sidebar SHALL highlight "Project" menu when on any /pages/project/* route

### Requirement 12: Breadcrumb Updates

**User Story:** As a user, I want breadcrumbs to show correct navigation path for Project and NOC pages, so that I understand my current location.

#### Acceptance Criteria

1. THE Project page breadcrumb SHALL display "Home > Project"
2. THE NOC list breadcrumb SHALL display "Home > Project > NOC"
3. THE NOC create breadcrumb SHALL display "Home > Project > NOC > Create"
4. THE NOC detail breadcrumb SHALL display "Home > Project > NOC > [NOC Number]"
5. THE NOC print breadcrumb SHALL display "Home > Project > NOC > [NOC Number] > Print"
6. THE System SHALL update all breadcrumb components in NOC views

### Requirement 13: Project Status Integration

**User Story:** As a system administrator, I want NOC status changes to update project status, so that project records reflect their NOC state.

#### Acceptance Criteria

1. WHEN a NOC is submitted THEN THE System SHALL update all included projects status to "NOC"
2. WHEN a NOC is deleted (Draft only) THEN THE System SHALL rollback all included projects status to "Active"
3. WHEN a NOC is approved THEN THE System SHALL maintain projects status as "NOC"
4. WHEN a NOC is rejected THEN THE System SHALL rollback all included projects status to "Active"
5. THE Project list SHALL highlight projects with "NOC" status using red background (#ffe6e6)
6. THE Project list SHALL disable Edit and Delete buttons for projects with "NOC" status

### Requirement 14: Data Migration Safety

**User Story:** As a system administrator, I want all existing NOC data preserved during restructuring, so that no information is lost.

#### Acceptance Criteria

1. THE migration SHALL create projects records for all approved pre-projects before restructuring NOCs
2. THE migration SHALL map all existing noc_pre_project records to corresponding noc_project records
3. THE migration SHALL preserve all NOC data including attachments, approvals, and status
4. THE migration SHALL maintain referential integrity between nocs, projects, and noc_project tables
5. THE migration SHALL be reversible with a rollback migration
6. THE migration SHALL NOT use db:wipe, migrate:fresh, or any destructive commands
7. THE migration SHALL validate data integrity after completion

### Requirement 15: NOC Functionality Preservation

**User Story:** As a NOC user, I want all existing NOC features to work exactly as before, so that the restructuring doesn't break functionality.

#### Acceptance Criteria

1. THE System SHALL preserve user-based access control (Parliament/DUN auto-detection)
2. THE System SHALL preserve two-level approval workflow (First Approver → Second Approver)
3. THE System SHALL preserve budget tracking and calculation functionality
4. THE System SHALL preserve import existing projects and add new projects functionality
5. THE System SHALL preserve all status management (Draft, Pending First Approval, Pending Second Approval, Approved, Rejected)
6. THE System SHALL preserve print functionality with A4 landscape format
7. THE System SHALL preserve file attachment functionality (NOC letter, project list)
8. THE System SHALL preserve all validation rules and business logic

### Requirement 16: Testing and Validation

**User Story:** As a developer, I want comprehensive tests to verify the restructuring works correctly, so that we can confidently deploy the changes.

#### Acceptance Criteria

1. THE System SHALL verify project transfer creates correct project records
2. THE System SHALL verify NOC creation works with projects table
3. THE System SHALL verify NOC import loads projects correctly
4. THE System SHALL verify NOC approval workflow functions correctly
5. THE System SHALL verify project status updates when NOC status changes
6. THE System SHALL verify all routes redirect to correct pages
7. THE System SHALL verify data migration completed without data loss
8. THE System SHALL verify all existing NOC features work with new structure
