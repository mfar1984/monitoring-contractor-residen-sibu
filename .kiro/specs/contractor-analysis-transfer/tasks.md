# Implementation Plan: Contractor Analysis Transfer System

## Overview

This implementation plan breaks down the Contractor Analysis Transfer System into discrete, incremental coding tasks. Each task builds on previous work and includes specific requirements references. The implementation follows Laravel best practices and integrates with the existing project structure.

## Tasks

- [x] 1. Set up database schema and migrations
  - Create migration for `contractor_analysis_transfers` table with all required fields
  - Create migration for `contractor_analysis_project` pivot table
  - Add "Analysis Pending" status to `status_masters` table if not exists
  - Run migrations to create tables
  - _Requirements: 5.1, 5.3, 5.4, 8.4_

- [x] 2. Create ContractorAnalysisTransfer model and relationships
  - [x] 2.1 Create ContractorAnalysisTransfer model with fillable fields and casts
    - Define fillable array: transfer_number, created_by, agency_category_id, attachment_path, status
    - Add datetime casts for created_at and updated_at
    - _Requirements: 5.1, 5.4_
  
  - [x] 2.2 Define model relationships
    - Add belongsTo relationship to User (creator)
    - Add belongsTo relationship to AgencyCategory
    - Add belongsToMany relationship to Project with pivot table
    - _Requirements: 5.4, 7.3_
  
  - [x] 2.3 Implement transfer number generation method
    - Create static method `generateTransferNumber()` that returns format CA/YYYY/###
    - Implement sequential numbering logic with year prefix
    - Ensure uniqueness by checking existing transfer numbers
    - _Requirements: 5.1, 12.1, 12.2, 12.3, 12.4_
  
  - [ ]* 2.4 Write property test for transfer number generation
    - **Property 6: Transfer Number Format and Uniqueness**
    - **Validates: Requirements 5.1, 12.1, 12.2, 12.3, 12.4**
  
  - [x] 2.5 Implement getAvailableProjects static method
    - Filter projects excluding "Analysis Pending" and "Cancelled" statuses
    - Apply agency filtering for Agency users
    - Return all projects for Residen users
    - Eager load relationships: agency, parliament, dun
    - _Requirements: 2.1, 2.2, 2.3_
  
  - [ ]* 2.6 Write property test for project filtering
    - **Property 1: Agency-Based Project Filtering**
    - **Validates: Requirements 2.1, 2.3**
  
  - [ ]* 2.7 Write property test for Residen user access
    - **Property 2: Residen User Full Access**
    - **Validates: Requirements 2.2, 6.2**

- [x] 3. Create ContractorAnalysisTransferService for business logic
  - [x] 3.1 Create service class with createTransfer method
    - Implement database transaction wrapper
    - Store attachment file with unique filename
    - Create transfer record with generated transfer number
    - Attach projects to transfer via pivot table
    - Update project statuses to "Analysis Pending"
    - Handle rollback on failure with file cleanup
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  
  - [ ]* 3.2 Write property test for transfer creation
    - **Property 7: Project Status Update on Transfer**
    - **Validates: Requirements 5.3**
  
  - [ ]* 3.3 Write property test for transfer metadata
    - **Property 8: Transfer Metadata Recording**
    - **Validates: Requirements 5.4**
  
  - [x] 3.4 Implement deleteTransfer method
    - Verify transfer status is "Draft"
    - Get project IDs before deletion
    - Rollback project statuses to "Active"
    - Delete attachment file from storage
    - Delete transfer record (cascade deletes pivot entries)
    - Wrap in database transaction
    - _Requirements: 8.1, 8.4, 8.5, 8.6_
  
  - [ ]* 3.5 Write property test for transfer deletion cleanup
    - **Property 14: Transfer Deletion Cleanup**
    - **Validates: Requirements 8.4, 8.5, 8.6**
  
  - [x] 3.6 Implement storeAttachment private method
    - Generate unique filename with timestamp
    - Store file in "contractor-analysis-attachments" directory
    - Use private disk for security
    - Return file path
    - _Requirements: 9.1, 9.2, 9.3_
  
  - [ ]* 3.7 Write property test for file storage
    - **Property 15: File Storage and Path Recording**
    - **Validates: Requirements 9.1, 9.2, 9.3**
  
  - [x] 3.8 Implement downloadAttachment method
    - Verify user has permission (Residen or matching agency)
    - Return file download response
    - Throw exception for unauthorized access
    - _Requirements: 9.4, 9.5_
  
  - [ ]* 3.9 Write property test for download authorization
    - **Property 16: Attachment Download Authorization**
    - **Validates: Requirements 9.4, 9.5**

- [ ] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Create form request validation
  - [x] 5.1 Create StoreContractorAnalysisTransferRequest class
    - Implement authorize method checking for Agency or Residen user
    - Define validation rules for project_ids (required, array, min:1)
    - Define validation rules for attachment (required, file, mimes:pdf,doc,docx, max:5120)
    - Add custom validation messages
    - _Requirements: 3.3, 4.2, 4.3, 4.4_
  
  - [x] 5.2 Add custom validator for agency project matching
    - Implement withValidator method
    - For Agency users, verify all selected projects match user's agency
    - Add error if any project has mismatched agency_category_id
    - _Requirements: 2.1, 10.4_
  
  - [ ]* 5.3 Write property test for validation errors
    - **Property 4: Multiple Project Selection Validation**
    - **Validates: Requirements 3.3**
  
  - [ ]* 5.4 Write property test for file validation
    - **Property 5: File Upload Validation**
    - **Validates: Requirements 4.2, 4.3, 4.4**
  
  - [ ]* 5.5 Write property test for agency validation
    - **Property 18: Agency Project Selection Validation**
    - **Validates: Requirements 10.4**

- [x] 6. Add controller methods to PageController
  - [x] 6.1 Implement contractorAnalysis method (list page)
    - Get authenticated user
    - Query transfers with relationships (creator, agency, projects)
    - Apply agency filtering for Agency users
    - Show all transfers for Residen users
    - Order by created_at descending
    - Return view with transfers data
    - _Requirements: 6.1, 6.2, 6.3_
  
  - [ ]* 6.2 Write property test for transfer list filtering
    - **Property 9: Agency-Based Transfer List Filtering**
    - **Validates: Requirements 6.1**
  
  - [x] 6.3 Implement contractorAnalysisCreate method (create form)
    - Check user authorization (Agency or Residen only)
    - Get available projects using model method
    - Get active agencies for Residen users
    - Return view with availableProjects and agencies
    - _Requirements: 1.1, 1.2, 2.1, 2.2_
  
  - [x] 6.4 Implement contractorAnalysisStore method (submit transfer)
    - Validate request using StoreContractorAnalysisTransferRequest
    - Instantiate ContractorAnalysisTransferService
    - Call createTransfer with validated data, file, and user
    - Redirect to list page with success message on success
    - Return back with error message on failure
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_
  
  - [x] 6.5 Implement contractorAnalysisShow method (detail page)
    - Find transfer by ID with relationships
    - Verify user has access (Residen or matching agency)
    - Return 403 if unauthorized
    - Return view with transfer data
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [x] 6.6 Implement contractorAnalysisDelete method
    - Find transfer by ID
    - Verify user has access (Residen or matching agency)
    - Call service deleteTransfer method
    - Redirect to list page with success message
    - Return back with error message on failure
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_
  
  - [x] 6.7 Implement contractorAnalysisDownload method
    - Find transfer by ID
    - Call service downloadAttachment method
    - Return file download response
    - Return 403 on authorization failure
    - _Requirements: 9.4, 9.5_

- [x] 7. Add routes to web.php
  - Add GET route for `/pages/contractor-analysis` → contractorAnalysis
  - Add GET route for `/pages/contractor-analysis/create` → contractorAnalysisCreate
  - Add POST route for `/pages/contractor-analysis` → contractorAnalysisStore
  - Add GET route for `/pages/contractor-analysis/{id}` → contractorAnalysisShow
  - Add DELETE route for `/pages/contractor-analysis/{id}` → contractorAnalysisDelete
  - Add GET route for `/pages/contractor-analysis/{id}/download` → contractorAnalysisDownload
  - Name all routes with `pages.contractor-analysis.*` prefix
  - _Requirements: All routes needed for feature_

- [x] 8. Create contractor analysis list page view
  - [x] 8.1 Create pages/contractor-analysis.blade.php
    - Extend layouts.app
    - Use data-table component with appropriate columns
    - Set title: "Contractor Analysis Transfer"
    - Set description: "Manage project transfers for contractor analysis"
    - Set createButtonText: "Create Transfer"
    - Set createButtonRoute to contractor-analysis.create
    - Define columns: Transfer Number, Agency, Projects Count, Status, Created Date, Actions
    - _Requirements: 6.3, 6.4, 11.1_
  
  - [x] 8.2 Implement table rows with transfer data
    - Display transfer_number as link to detail page
    - Display agency name from relationship
    - Display projects count using count() method
    - Display status with color-coded badge
    - Display created_at formatted as date
    - Add View action button for all transfers
    - Add Delete action button only for Draft status
    - _Requirements: 6.3, 8.1, 8.2_
  
  - [ ]* 8.3 Write property test for complete data display
    - **Property 10: Transfer List Complete Data Display**
    - **Validates: Requirements 6.3**
  
  - [x] 8.4 Add search functionality for transfer numbers
    - Implement search input in data-table component
    - Filter transfers by transfer_number
    - _Requirements: 6.5_
  
  - [ ]* 8.5 Write property test for search functionality
    - **Property 11: Transfer Search Functionality**
    - **Validates: Requirements 6.5**

- [x] 9. Create transfer creation form view
  - [x] 9.1 Create pages/contractor-analysis-create.blade.php
    - Extend layouts.app
    - Add page header with title and description
    - Create form with POST action to contractor-analysis.store
    - Include CSRF token
    - Display validation errors if present
    - _Requirements: 10.1, 10.2_
  
  - [x] 9.2 Implement project selection table
    - Create table with columns: Checkbox, Project Number, Project Name, Total Cost, Status
    - Add checkbox input for each project with name="project_ids[]"
    - Display project data from availableProjects variable
    - Use forms.css for consistent styling
    - _Requirements: 2.4, 3.1, 3.2_
  
  - [ ]* 9.3 Write property test for required fields display
    - **Property 3: Required Fields Display**
    - **Validates: Requirements 2.4**
  
  - [x] 9.4 Implement attachment upload field
    - Add file input with name="attachment"
    - Add label "Surat Permohonan / Application Letter"
    - Add accept attribute for PDF, DOC, DOCX
    - Display file validation requirements (format, size limit)
    - Use forms.css for consistent styling
    - _Requirements: 4.1, 4.2, 4.3, 4.4_
  
  - [x] 9.5 Add form action buttons
    - Add Submit button with primary button styling
    - Add Cancel button linking back to list page
    - Use buttons.css for consistent styling
    - _Requirements: 11.4_

- [x] 10. Create transfer detail view
  - [x] 10.1 Create pages/contractor-analysis-show.blade.php
    - Extend layouts.app
    - Display transfer information section (number, agency, date, status, creator)
    - Display projects table with all project details
    - Add download link for attachment
    - Add Back to List button
    - Add Delete button if status is Draft
    - _Requirements: 7.2, 7.3, 7.4, 7.5, 8.1_
  
  - [ ]* 10.2 Write property test for detail page data display
    - **Property 12: Transfer Detail Complete Data Display**
    - **Validates: Requirements 7.2, 7.3, 7.5**
  
  - [ ]* 10.3 Write property test for delete button visibility
    - **Property 13: Delete Button Conditional Display**
    - **Validates: Requirements 8.1, 8.2**

- [x] 11. Add navigation link to sidebar
  - Open resources/views/components/layout/sidebar.blade.php
  - Add "Contractor Analysis" menu item under appropriate section
  - Link to route('pages.contractor-analysis')
  - Use appropriate icon
  - _Requirements: Navigation integration_

- [ ] 12. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 13. Create database seeders for testing
  - [ ] 13.1 Create ContractorAnalysisTransferSeeder
    - Create sample transfers for different agencies
    - Create sample projects in various statuses
    - Link projects to transfers via pivot table
    - Create sample attachment files
    - _Requirements: Testing data_
  
  - [ ]* 13.2 Write unit tests for transfer creation workflow
    - Test successful transfer creation with valid data
    - Test validation failures for missing fields
    - Test authorization for different user types
    - _Requirements: 1.1, 1.2, 3.3, 4.2_
  
  - [ ]* 13.3 Write unit tests for transfer deletion workflow
    - Test successful deletion of draft transfer
    - Test prevention of non-draft deletion
    - Test project status rollback
    - Test file cleanup
    - _Requirements: 8.1, 8.4, 8.5, 8.6_
  
  - [ ]* 13.4 Write integration tests for complete user workflows
    - Test Agency user creating transfer with own agency projects
    - Test Residen user creating transfer with any projects
    - Test viewing transfer details
    - Test downloading attachments
    - _Requirements: End-to-end workflows_

- [x] 14. Add file storage configuration
  - Verify private disk is configured in config/filesystems.php
  - Create contractor-analysis-attachments directory in storage
  - Set appropriate permissions for file storage
  - _Requirements: 9.1_

- [ ] 15. Final checkpoint - Ensure all tests pass and feature is complete
  - Run all unit tests and property tests
  - Verify all 19 correctness properties are tested
  - Test complete user workflows in browser
  - Verify access control for all user types
  - Verify file upload and download functionality
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional property-based and unit tests that can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties with minimum 100 iterations
- Unit tests validate specific examples, edge cases, and error conditions
- The implementation follows existing design patterns from Project Transfer and NOC systems
- All pages use existing CSS components (forms.css, table.css, buttons.css) for consistency
- Access control is enforced at multiple layers: authorization, controller, and service
