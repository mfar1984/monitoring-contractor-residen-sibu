# Implementation Plan: NOC System Restructuring

## Overview

This implementation plan restructures the NOC system from Pre-Project to Project module through a series of incremental steps. The approach prioritizes data safety by creating new structures before migrating data, then updating code references, and finally cleaning up old structures. Each step builds on previous work and includes validation checkpoints.

## Tasks

- [x] 1. Create Project model and database infrastructure
  - Create Project model with all fields from PreProject
  - Create migration for projects table with proper schema
  - Add project_number, pre_project_id, approval_date, transferred_at fields
  - Define all relationships (residenCategory, agencyCategory, parliament, etc.)
  - Implement generateProjectNumber() static method
  - Implement scopeForParliament(), scopeForDun(), scopeForUser() query scopes
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

- [ ]* 1.1 Write property test for project number uniqueness
  - **Property 1: Project Number Uniqueness**
  - **Validates: Requirements 1.2, 2.2**

- [ ]* 1.2 Write property test for project number format
  - **Property 2: Project Number Format Compliance**
  - **Validates: Requirements 2.7**

- [x] 2. Create ProjectTransferService
  - [x] 2.1 Create ProjectTransferService class in app/Services
    - Implement transfer() method to convert approved pre-project to project
    - Implement canTransfer() method to validate transfer eligibility
    - Implement getProjectForPreProject() method to check existing transfers
    - Add validation for approved status
    - Add duplicate transfer prevention
    - Copy all pre-project data to project
    - Generate and assign project number
    - Set approval_date and transferred_at timestamps
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_
  
  - [ ]* 2.2 Write property test for project transfer completeness
    - **Property 3: Project Transfer Completeness**
    - **Validates: Requirements 2.3, 2.4**
  
  - [ ]* 2.3 Write property test for transfer trigger
    - **Property 4: Project Transfer Trigger**
    - **Validates: Requirements 2.1**
  
  - [ ]* 2.4 Write property test for timestamp initialization
    - **Property 5: Transfer Timestamp Initialization**
    - **Validates: Requirements 2.5, 2.6**
  
  - [ ]* 2.5 Write unit tests for ProjectTransferService
    - Test transfer of approved pre-project succeeds
    - Test transfer of non-approved pre-project throws exception
    - Test transfer of already-transferred pre-project returns existing
    - Test error handling for edge cases
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_

- [ ] 3. Checkpoint - Verify project transfer works
  - Run migrations to create projects table
  - Test ProjectTransferService with sample pre-projects
  - Verify project numbers generate correctly
  - Verify all data copies correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Create data migration for approved pre-projects
  - [x] 4.1 Create migration to transfer existing approved pre-projects
    - Query all pre-projects with status "Approved"
    - Use ProjectTransferService to transfer each pre-project
    - Log any transfer failures for manual review
    - Verify all approved pre-projects have corresponding projects
    - _Requirements: 14.1_
  
  - [ ]* 4.2 Write property test for migration completeness
    - **Property 14: Migration Data Completeness**
    - **Validates: Requirements 14.1, 14.2**
  
  - [ ]* 4.3 Write unit tests for pre-project migration
    - Test migration creates projects for all approved pre-projects
    - Test migration skips non-approved pre-projects
    - Test migration handles duplicate transfers gracefully
    - _Requirements: 14.1_

- [x] 5. Create and migrate NOC pivot table
  - [x] 5.1 Create migration to rename noc_pre_project to noc_project
    - Rename table from noc_pre_project to noc_project
    - _Requirements: 3.1_
  
  - [x] 5.2 Create migration to update foreign key column
    - Drop foreign key constraint on pre_project_id
    - Rename pre_project_id column to project_id
    - Add foreign key constraint on project_id referencing projects.id
    - _Requirements: 3.2, 3.3_
  
  - [x] 5.3 Create migration to map NOC data to projects
    - For each noc_project record, find corresponding project via pre_project_id
    - Update project_id to reference the correct project
    - Log any unmapped records for manual review
    - Verify all noc_project records have valid project_id
    - _Requirements: 3.7_
  
  - [ ]* 5.4 Write property test for NOC data migration mapping
    - **Property 6: NOC Data Migration Mapping**
    - **Validates: Requirements 3.7**
  
  - [ ]* 5.5 Write property test for migration data preservation
    - **Property 15: Migration Data Preservation**
    - **Validates: Requirements 14.3**
  
  - [ ]* 5.6 Write property test for referential integrity
    - **Property 16: Migration Referential Integrity**
    - **Validates: Requirements 14.4, 14.7**
  
  - [ ]* 5.7 Write unit tests for NOC pivot migration
    - Test table rename succeeds
    - Test foreign key update succeeds
    - Test data mapping completes without orphans
    - Test rollback migration works correctly
    - _Requirements: 3.1, 3.2, 3.3, 3.7_

- [ ] 6. Checkpoint - Verify database migrations complete
  - Run all migrations in sequence
  - Verify projects table populated with approved pre-projects
  - Verify noc_project table exists with correct schema
  - Verify all NOC data mapped to projects
  - Verify no orphaned records
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Update Noc model relationships
  - [x] 7.1 Update Noc model to use Project instead of PreProject
    - Remove preProjects() relationship method
    - Add projects() belongsToMany relationship using noc_project pivot
    - Include all pivot fields in withPivot()
    - Update getAvailableProjects() to query projects table
    - Filter by user parliament/DUN
    - Exclude projects already in NOCs
    - Only include active projects
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  
  - [x] 7.2 Update Project model to add NOC relationship
    - Add nocs() belongsToMany relationship
    - _Requirements: 4.5_
  
  - [ ]* 7.3 Write property test for pivot data preservation
    - **Property 7: Pivot Data Preservation**
    - **Validates: Requirements 4.6**
  
  - [ ]* 7.4 Write unit tests for model relationships
    - Test Noc->projects() returns correct projects
    - Test Project->nocs() returns correct NOCs
    - Test pivot data accessible through relationship
    - Test getAvailableProjects() filters correctly
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_

- [x] 8. Update NOC routes to project namespace
  - [x] 8.1 Update routes in web.php
    - Change route prefix from /pages/pre-project/noc to /pages/project/noc
    - Update all NOC route names from pages.pre-project.noc.* to pages.project.noc.*
    - Add redirect routes from old URLs to new URLs (301 permanent)
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10, 5.11_
  
  - [ ]* 8.2 Write unit tests for route updates
    - Test all new routes resolve correctly
    - Test old routes redirect to new routes
    - Test route parameters work correctly
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10, 5.11_

- [x] 9. Update NOC controller methods
  - [x] 9.1 Rename and update controller methods in PageController
    - Rename preProjectNoc() to projectNoc()
    - Update to query Noc with projects relationship
    - Rename preProjectNocCreate() to projectNocCreate()
    - Update to load projects instead of pre-projects using Noc::getAvailableProjects()
    - Rename preProjectNocStore() to projectNocStore()
    - Update to attach projects instead of pre-projects
    - Update validation rules to use project_id
    - Rename preProjectNocShow() to projectNocShow()
    - Update to load NOC with projects relationship
    - Rename preProjectNocSubmit() to projectNocSubmit()
    - Update to change project status to "NOC" when submitted
    - Rename preProjectNocApprove() to projectNocApprove()
    - Rename preProjectNocReject() to projectNocReject()
    - Update to rollback project status to "Active" when rejected
    - Rename preProjectNocPrint() to projectNocPrint()
    - Update to load NOC with projects relationship
    - Rename preProjectNocDelete() to projectNocDelete()
    - Update to rollback project status to "Active" when deleted
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8, 6.9, 6.10, 6.11_
  
  - [ ]* 9.2 Write property test for NOC status synchronization
    - **Property 13: NOC Status Synchronization**
    - **Validates: Requirements 13.1, 13.2, 13.3, 13.4**
  
  - [ ]* 9.3 Write unit tests for controller methods
    - Test projectNoc() returns NOCs with projects
    - Test projectNocCreate() loads available projects
    - Test projectNocStore() creates NOC with projects
    - Test projectNocSubmit() updates project status
    - Test projectNocReject() rollbacks project status
    - Test projectNocDelete() rollbacks project status
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8, 6.9, 6.10, 6.11, 13.1, 13.2, 13.3, 13.4_

- [ ] 10. Checkpoint - Verify backend updates work
  - Test all NOC controller methods with sample data
  - Verify NOCs load projects correctly
  - Verify NOC creation attaches to projects
  - Verify status synchronization works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Rename and update NOC view files
  - [x] 11.1 Rename view files
    - Rename pre-project-noc.blade.php to project-noc.blade.php
    - Rename pre-project-noc-create.blade.php to project-noc-create.blade.php
    - Rename pre-project-noc-show.blade.php to project-noc-show.blade.php
    - Rename pre-project-noc-print.blade.php to project-noc-print.blade.php
    - _Requirements: 7.1, 7.2, 7.3, 7.4_
  
  - [x] 11.2 Update route references in all NOC views
    - Update all route() calls from pages.pre-project.noc.* to pages.project.noc.*
    - Update all form actions to use new routes
    - Update all redirect URLs to use new routes
    - Update breadcrumbs to show "Home > Project > NOC" path
    - _Requirements: 7.5, 7.6, 7.7, 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_
  
  - [x] 11.3 Update data references in NOC views
    - Update references from $noc->preProjects to $noc->projects
    - Update import modal to display project data
    - Update table columns to show project information
    - _Requirements: 7.8_
  
  - [ ]* 11.4 Write unit tests for view rendering
    - Test project-noc view renders with NOC data
    - Test project-noc-create view renders with available projects
    - Test project-noc-show view renders with project details
    - Test breadcrumbs display correctly
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8, 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_

- [x] 12. Create Project page with tabs
  - [x] 12.1 Create project-tabs component
    - Create resources/views/components/project-tabs.blade.php
    - Add three tabs: Project, NOC, Project Cancel
    - Accept 'active' prop to highlight current tab
    - Use same styling as master-data-tabs component
    - Support horizontal drag scrolling
    - _Requirements: 8.2, 8.6, 8.7, 8.8_
  
  - [x] 12.2 Create main project page view
    - Create resources/views/pages/project.blade.php
    - Include project-tabs component
    - Display project list using data-table component
    - Show columns: Project Number, Project Name, Parliament/DUN, Total Cost, Approval Date, Status, Actions
    - Filter projects by user's parliament_id or dun_id
    - Add View action button for each project
    - Highlight projects with "NOC" status (red background)
    - Disable Edit/Delete buttons for "NOC" status projects
    - _Requirements: 8.1, 8.2, 8.3, 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 13.5, 13.6_
  
  - [x] 12.3 Create project page route and controller method
    - Add route GET /pages/project
    - Create project() method in PageController
    - Load projects filtered by user parliament/DUN
    - Pass projects to view
    - _Requirements: 8.1_
  
  - [x] 12.4 Update NOC views to include project-tabs component
    - Add project-tabs component to project-noc.blade.php with active="noc"
    - Add project-tabs component to project-noc-create.blade.php with active="noc"
    - Add project-tabs component to project-noc-show.blade.php with active="noc"
    - _Requirements: 8.2, 8.4_
  
  - [x] 12.5 Create placeholder for Project Cancel tab
    - Create resources/views/pages/project-cancel.blade.php
    - Display placeholder message "Project Cancel functionality coming soon"
    - Include project-tabs component with active="cancel"
    - _Requirements: 8.5_
  
  - [ ]* 12.6 Write property test for user-based project filtering
    - **Property 8: User-Based Project Filtering**
    - **Validates: Requirements 9.3, 9.7**
  
  - [ ]* 12.7 Write property test for project search
    - **Property 9: Project Search Functionality**
    - **Validates: Requirements 9.4**
  
  - [ ]* 12.8 Write property test for active tab highlighting
    - **Property 12: Active Tab Highlighting**
    - **Validates: Requirements 8.7, 11.5**
  
  - [ ]* 12.9 Write unit tests for project page
    - Test project page renders with tabs
    - Test project tab displays project list
    - Test NOC tab displays NOC list
    - Test cancel tab displays placeholder
    - Test active tab highlighting works
    - Test project filtering by user parliament/DUN
    - Test NOC status projects highlighted
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.7, 9.1, 9.2, 9.3, 9.6, 9.7, 13.5, 13.6_

- [x] 13. Update sidebar navigation
  - [x] 13.1 Add Project menu item to sidebar
    - Add "Project" menu item at same level as "Pre-Project"
    - Link to route('pages.project')
    - Add icon (use same style as other menu items)
    - Highlight when on /pages/project/* routes
    - _Requirements: 11.1, 11.2, 11.5_
  
  - [x] 13.2 Remove NOC submenu from Pre-Project
    - Remove "NOC" submenu item from under "Pre-Project"
    - _Requirements: 11.3_
  
  - [ ]* 13.3 Write unit tests for sidebar updates
    - Test sidebar contains "Project" menu item
    - Test "Project" menu links to correct route
    - Test "Project" menu highlights on project routes
    - Test "NOC" submenu removed from "Pre-Project"
    - _Requirements: 11.1, 11.2, 11.3, 11.5_

- [x] 14. Update NOC import functionality
  - [x] 14.1 Update NOC create page to load projects
    - Update import modal to query projects table
    - Display Project Number, Project Name, Total Cost
    - Filter by user parliament/DUN
    - Exclude projects already in NOCs
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [ ]* 14.2 Write property test for available projects exclusion
    - **Property 10: Available Projects Exclusion**
    - **Validates: Requirements 10.3**
  
  - [ ]* 14.3 Write property test for user-based NOC import filtering
    - **Property 11: User-Based NOC Import Filtering**
    - **Validates: Requirements 10.2**
  
  - [ ]* 14.4 Write unit tests for NOC import
    - Test import modal loads projects not pre-projects
    - Test import modal filters by user parliament/DUN
    - Test import modal excludes projects in NOCs
    - Test import populates project data correctly
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 15. Checkpoint - Verify frontend updates work
  - Test project page displays correctly
  - Test all three tabs work
  - Test NOC pages display correctly
  - Test sidebar navigation works
  - Test NOC import loads projects
  - Ensure all tests pass, ask the user if questions arise.

- [x] 16. Integration testing and validation
  - [x] 16.1 Write end-to-end integration tests
    - Test complete workflow: create pre-project → approve → verify project created
    - Test complete workflow: create NOC with project → submit → verify status change
    - Test complete workflow: delete NOC → verify status rollback
    - Test navigation: project page → NOC tab → NOC create → back to list
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6, 16.7, 16.8_
  
  - [x] 16.2 Verify all existing NOC functionality preserved
    - Test user-based access control works
    - Test two-level approval workflow works
    - Test budget tracking and calculation works
    - Test import and add new projects works
    - Test status management works
    - Test print functionality works
    - Test file attachments work
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 15.7, 15.8_
  
  - [x] 16.3 Run full test suite
    - Run all unit tests
    - Run all property-based tests (100 iterations each)
    - Run all integration tests
    - Verify 100% pass rate
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6, 16.7, 16.8_

- [x] 17. Final checkpoint and deployment preparation
  - Verify all migrations run successfully
  - Verify no data loss occurred
  - Verify all routes work correctly
  - Verify all views render correctly
  - Verify all tests pass
  - Document any manual steps needed for deployment
  - Ensure all tests pass, ask the user if questions arise.

- [x] 18. Implement NOC to Pre-Project integration
  - [x] 18.1 Create NocToPreProjectService
    - Create app/Services/NocToPreProjectService.php
    - Implement processNocSubmission() method
    - Implement hasChanges() method to detect if project has modifications
    - Implement createPreProjectFromNocChanges() method
    - Handle project name changes (use new name if provided, otherwise original)
    - Handle cost changes (use new cost if provided, otherwise original)
    - Handle agency changes (use new agency if provided, otherwise original)
    - Set status to "Waiting For EPU Approval"
    - Keep original Project Number in pre-project record
    - Skip "Add New" projects (those without Project Number)
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5, 17.6, 17.7, 17.8, 17.11, 17.12, 17.13_
  
  - [ ]* 18.2 Write property test for NOC change detection
    - **Property 17: NOC Change Detection**
    - **Validates: Requirements 17.13**
  
  - [ ]* 18.3 Write property test for pre-project creation from NOC
    - **Property 18: Pre-Project Creation from NOC Changes**
    - **Validates: Requirements 17.2, 17.3**
  
  - [ ]* 18.4 Write property test for pre-project data accuracy
    - **Property 19: Pre-Project Data Accuracy**
    - **Validates: Requirements 17.4, 17.5, 17.6, 17.7, 17.8**
  
  - [ ]* 18.5 Write property test for new project exclusion
    - **Property 20: New Project Exclusion**
    - **Validates: Requirements 17.12**
  
  - [ ]* 18.6 Write property test for pre-project visibility
    - **Property 21: Pre-Project Visibility**
    - **Validates: Requirements 17.9, 17.10, 17.11**
  
  - [ ]* 18.7 Write unit tests for NocToPreProjectService
    - Test hasChanges() correctly detects changes
    - Test hasChanges() returns false when no changes
    - Test createPreProjectFromNocChanges() creates correct record
    - Test processNocSubmission() creates pre-projects for changed imports
    - Test processNocSubmission() skips "Add New" projects
    - Test error handling for invalid data
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5, 17.6, 17.7, 17.8, 17.12, 17.13_

- [x] 19. Update NOC submission to integrate with Pre-Project
  - [x] 19.1 Update projectNocSubmit() controller method
    - Inject NocToPreProjectService
    - Call processNocSubmission() after updating NOC status
    - Log created pre-projects for tracking
    - Add success message indicating pre-projects created
    - _Requirements: 17.2, 17.3_
  
  - [ ]* 19.2 Write integration tests for NOC submission with pre-project creation
    - Test NOC submission creates pre-projects for changed imports
    - Test NOC submission skips unchanged imports
    - Test NOC submission skips "Add New" projects
    - Test created pre-projects visible in Pre-Project list
    - Test created pre-projects have correct status
    - Test created pre-projects have Project Number
    - _Requirements: 17.2, 17.3, 17.9, 17.10, 17.11, 17.12_

- [x] 20. Update Pre-Project model and migration
  - [x] 20.1 Add project_number field to pre_projects table
    - Create migration to add project_number VARCHAR(255) NULL
    - Add index on project_number for faster queries
    - Update PreProject model fillable array
    - _Requirements: 17.4, 17.11_
  
  - [ ]* 20.2 Write unit tests for pre-project with project number
    - Test pre-project can be created with project_number
    - Test pre-project can be queried by project_number
    - Test pre-project list displays project_number
    - _Requirements: 17.4, 17.11_

- [x] 21. Update Pre-Project list view
  - [x] 21.1 Update pre-project.blade.php to display Project Number
    - Add "Project No" column to data-table
    - Display project_number field if present, otherwise "N/A"
    - Highlight rows with status "Waiting For EPU Approval"
    - _Requirements: 17.9, 17.10, 17.11_
  
  - [ ]* 21.2 Write unit tests for pre-project list display
    - Test Project Number column displays correctly
    - Test "Waiting For EPU Approval" status displays correctly
    - Test rows with project numbers are visible
    - _Requirements: 17.9, 17.10, 17.11_

- [ ] 22. Final integration testing for Pre-Project workflow
  - [ ] 22.1 Write end-to-end tests for complete workflow
    - Test: Import project → Change name → Submit NOC → Verify pre-project created
    - Test: Import project → Change cost → Submit NOC → Verify pre-project created
    - Test: Import project → Change agency → Submit NOC → Verify pre-project created
    - Test: Import project → No changes → Submit NOC → Verify no pre-project created
    - Test: Add new project → Submit NOC → Verify no pre-project created
    - Test: Created pre-project visible in Pre-Project list
    - Test: Created pre-project has correct status and Project Number
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5, 17.6, 17.7, 17.8, 17.9, 17.10, 17.11, 17.12, 17.13_
  
  - [ ] 22.2 Manual testing checklist
    - Create NOC with imported project with name change
    - Submit NOC
    - Navigate to Pre-Project list
    - Verify new pre-project appears with "Waiting For EPU Approval"
    - Verify Project Number is populated
    - Verify new project name is used
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5, 17.9, 17.10, 17.11_

- [ ] 23. Documentation and deployment
  - Document the NOC to Pre-Project workflow
  - Update user guide with new workflow
  - Document database schema changes
  - Prepare deployment checklist
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional property-based and unit tests that can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation throughout implementation
- Migration tasks prioritize data safety (create before migrate, verify before delete)
- All existing NOC functionality must be preserved during restructuring
- No destructive database commands (db:wipe, migrate:fresh) should be used
