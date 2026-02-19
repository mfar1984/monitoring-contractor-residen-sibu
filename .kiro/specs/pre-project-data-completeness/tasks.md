# Implementation Plan: Pre-Project Data Completeness

## Overview

This implementation plan breaks down the Pre-Project Data Completeness feature into discrete coding tasks. The approach follows this sequence:

1. Add database fields for submission tracking
2. Extend PreProject model with completeness calculation methods
3. Add controller method for EPU submission
4. Update Pre-Project list view with completeness column and Submit button
5. Add missing fields modal
6. Add route for submission endpoint
7. Write tests for completeness calculation and validation

Each task builds on previous work and includes specific requirements references for traceability.

## Tasks

- [x] 1. Create database migration for submission tracking fields
  - Add `submitted_to_epu_at` timestamp field (nullable)
  - Add `submitted_to_epu_by` foreign key field (nullable, references users.id)
  - Add foreign key constraint with onDelete('set null')
  - Update PreProject model fillable array to include new fields
  - _Requirements: 4.6, 5.5_

- [x] 2. Implement completeness calculation methods in PreProject model
  - [x] 2.1 Add getRequiredFieldsDefinition() method
    - Return array with 9 required fields and their display names
    - Fields: project_scope, project_category_id, implementation_period, division_id, district_id, land_title_status_id, implementing_agency_id, implementation_method_id, project_ownership_id
    - _Requirements: 2.1_
  
  - [ ]* 2.2 Write property test for getRequiredFieldsDefinition()
    - **Property 1: Completeness Percentage Calculation**
    - **Validates: Requirements 1.2, 2.2, 7.2, 7.5, 7.6**
  
  - [x] 2.3 Add getCompletenessPercentage() method
    - Calculate percentage of filled required fields
    - Return integer between 0 and 100
    - Handle edge case of zero required fields (return 100)
    - _Requirements: 1.2, 7.1, 7.2_
  
  - [x] 2.4 Add getMissingRequiredFields() method
    - Return array of display names for empty/null required fields
    - Check each field from getRequiredFieldsDefinition()
    - _Requirements: 7.3, 7.4_
  
  - [ ]* 2.5 Write property test for getMissingRequiredFields()
    - **Property 4: Missing Fields Array Accuracy**
    - **Validates: Requirements 6.3, 7.4**
  
  - [x] 2.6 Add isDataComplete() method
    - Return true if completeness is 100%, false otherwise
    - _Requirements: 4.1_
  
  - [x] 2.7 Add getCompletenessBadgeColor() method
    - Return '#dc3545' (red) for 0-50%
    - Return '#ffc107' (yellow) for 51-80%
    - Return '#28a745' (green) for 81-100%
    - _Requirements: 1.3, 1.4, 1.5_
  
  - [ ]* 2.8 Write property test for color coding
    - **Property 2: Color Coding Based on Percentage**
    - **Validates: Requirements 1.3, 1.4, 1.5**

- [x] 3. Add EPU submission controller method
  - [x] 3.1 Create preProjectSubmitToEpu() method in PageController
    - Check user authorization (parliament_category_id or dun_basic_id required)
    - Validate Pre-Project status is "Waiting for EPU Approval"
    - Call isDataComplete() to validate completeness
    - If incomplete, redirect with error and missing_fields session data
    - If complete, update status to "Submitted to EPU"
    - Record submitted_to_epu_at timestamp and submitted_to_epu_by user ID
    - Redirect with success message
    - _Requirements: 3.3, 3.4, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_
  
  - [ ]* 3.2 Write unit test for unauthorized submission attempt
    - Test user without parliament_category_id cannot submit
    - _Requirements: 3.4_
  
  - [ ]* 3.3 Write property test for validation prevents incomplete submission
    - **Property 7: Validation Prevents Incomplete Submission**
    - **Validates: Requirements 4.1, 4.2, 4.3**
  
  - [ ]* 3.4 Write property test for complete submission changes status
    - **Property 8: Complete Submission Changes Status**
    - **Validates: Requirements 4.4, 4.6**

- [x] 4. Update preProject() method to include completeness data
  - Loop through $preProjects collection
  - Add completeness_percentage property using getCompletenessPercentage()
  - Add completeness_color property using getCompletenessBadgeColor()
  - Pass data to view
  - _Requirements: 1.2, 1.3, 1.4, 1.5_

- [x] 5. Add route for EPU submission
  - Add POST route: /pages/pre-project/{id}/submit-to-epu
  - Route name: pages.pre-project.submit-to-epu
  - Controller: PageController@preProjectSubmitToEpu
  - Add to web.php routes file
  - _Requirements: 4.1_

- [x] 6. Update Pre-Project list view with completeness column
  - [x] 6.1 Add "Completeness" column header after "Status" column
    - _Requirements: 1.1_
  
  - [x] 6.2 Add completeness indicator in table body
    - Show percentage badge for "Waiting for EPU Approval" status
    - Use completeness_color for badge background
    - Display completeness_percentage with "%" suffix
    - Show "N/A" for other statuses
    - _Requirements: 1.6, 1.7_
  
  - [ ]* 6.3 Write unit test for completeness column display
    - Test column appears in rendered HTML
    - _Requirements: 1.1_

- [x] 7. Update actions column with Submit to EPU button
  - [x] 7.1 Add conditional logic for button display
    - Show "Submit to EPU" button when status is "Waiting for EPU Approval"
    - Show button only for users with parliament_category_id or dun_basic_id
    - Hide Edit and Delete buttons when Submit button is shown
    - Keep Print button always visible
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 7.2 Create Submit to EPU button with form
    - POST form to route('pages.pre-project.submit-to-epu', $preProject->id)
    - Include @csrf token
    - Use Material Symbols icon "send"
    - Style as primary button (btn-primary)
    - _Requirements: 3.1_
  
  - [ ]* 7.3 Write property test for submit button visibility
    - **Property 5: Submit Button Visibility Based on Status**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
  
  - [ ]* 7.4 Write property test for button replacement
    - **Property 6: Button Replacement for Waiting Status**
    - **Validates: Requirements 3.5**

- [x] 8. Create missing fields modal
  - [x] 8.1 Add modal HTML structure
    - Modal ID: missingFieldsModal
    - Modal title: "Incomplete Data"
    - Empty list element with ID: missingFieldsList
    - Close button with onclick handler
    - Footer with Close button
    - Initially hidden (display: none)
    - _Requirements: 6.1, 6.2, 6.4_
  
  - [x] 8.2 Add JavaScript to show modal on validation failure
    - Check for session('missing_fields')
    - Call showMissingFieldsModal() on page load if present
    - Populate missingFieldsList with missing field names
    - Display modal (display: flex)
    - _Requirements: 6.1, 6.3_
  
  - [x] 8.3 Add JavaScript to close modal
    - closeMissingFieldsModal() function
    - Hide modal (display: none)
    - Close on ESC key press
    - Close when clicking outside modal
    - _Requirements: 6.4, 6.5_
  
  - [ ]* 8.4 Write unit test for modal display on validation failure
    - Test modal appears when missing_fields in session
    - Test modal contains all missing field names
    - _Requirements: 6.1, 6.3_
  
  - [ ]* 8.5 Write property test for modal dismissal preserves status
    - **Property 13: Modal Dismissal Preserves Status**
    - **Validates: Requirements 6.5**

- [ ] 9. Checkpoint - Ensure all tests pass
  - Run php artisan test
  - Verify completeness calculation works correctly
  - Verify Submit button appears for correct users and statuses
  - Verify validation prevents incomplete submissions
  - Verify modal displays missing fields
  - Ask the user if questions arise

- [ ] 10. Update edit authorization logic
  - [ ] 10.1 Modify preProjectEdit() to check status
    - Block edit requests for "Submitted to EPU" status
    - Return error message for unauthorized edit attempts
    - _Requirements: 5.3_
  
  - [ ]* 10.2 Write property test for edit authorization
    - **Property 10: Edit Authorization Based on Status**
    - **Validates: Requirements 5.2, 5.3**

- [ ] 11. Add EPU approver authorization check
  - [ ] 11.1 Create method to check EPU approver status
    - Get EPU approvers from integration_settings
    - Check if current user is in approvers list
    - _Requirements: 5.4_
  
  - [ ] 11.2 Update approval methods to check EPU approver status
    - Modify existing approval logic to check for "Submitted to EPU" status
    - Only allow EPU approvers to approve/reject at this stage
    - _Requirements: 5.4_
  
  - [ ]* 11.3 Write property test for EPU approver authorization
    - **Property 11: EPU Approver Authorization**
    - **Validates: Requirements 5.4**

- [ ] 12. Add audit trail for status changes
  - [ ] 12.1 Create status change logging
    - Log user ID and timestamp for all status changes
    - Store in existing submitted_to_epu_at and submitted_to_epu_by fields
    - _Requirements: 5.5_
  
  - [ ]* 12.2 Write property test for audit trail
    - **Property 12: Audit Trail Completeness**
    - **Validates: Requirements 5.5**

- [ ] 13. Integration testing
  - [ ]* 13.1 Write integration test for complete workflow
    - Create Pre-Project from NOC with status "Waiting for EPU Approval"
    - Verify completeness < 100%
    - Attempt submission → validation fails
    - Complete all required fields
    - Verify completeness = 100%
    - Submit to EPU → status changes
    - Verify timestamp and user ID recorded
    - Verify Member of Parliament cannot edit after submission
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.6, 5.1, 5.2, 5.3_

- [ ] 14. Final checkpoint - Ensure all tests pass
  - Run php artisan test
  - Test complete workflow in browser
  - Verify all requirements are met
  - Ask the user if questions arise

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties across all inputs
- Unit tests validate specific examples and edge cases
- The existing pre_projects table structure supports all required fields
- No changes needed to master data tables
- Modal styling should match existing delete confirmation modal
- Color scheme follows existing application standards
