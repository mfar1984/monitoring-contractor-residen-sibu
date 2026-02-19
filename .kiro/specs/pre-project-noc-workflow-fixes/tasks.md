# Implementation Plan: Pre-Project and NOC Workflow Fixes

## Overview

This implementation plan documents the fixes applied to correct the Pre-Project approval workflow and NOC budget validation. These tasks have been completed and are documented here for reference and testing purposes.

## Tasks

- [x] 1. Fix Pre-Project approval workflow to skip Approver 2
  - [x] 1.1 Update preProjectApprove() method in PageController
    - Change status transition from "Waiting for Approver 1" directly to "Waiting for EPU Approval"
    - Remove any logic that sets status to "Waiting for Approver 2"
    - Verify user is in Pre-Project Approvers list before allowing approval
    - Record approval timestamp and user ID
    - _Requirements: 1.1, 1.2_
  
  - [x] 1.2 Update preProjectReject() method in PageController
    - Check status is "Waiting for Approver 1" only (not "Waiting for Approver 2")
    - Remove any logic that checks for "Waiting for Approver 2" status
    - Verify user is in Pre-Project Approvers list before allowing rejection
    - _Requirements: 1.6_
  
  - [ ]* 1.3 Write unit test for single approval workflow
    - Test approval changes status to "Waiting for EPU Approval"
    - Test status never becomes "Waiting for Approver 2"
    - _Requirements: 1.2_

- [x] 2. Update Pre-Project approval modal to show static text
  - [x] 2.1 Remove dynamic text logic from JavaScript
    - Delete JavaScript code that changes modal text based on status
    - Remove status-based conditional logic for modal content
    - _Requirements: 1.5, 8.3_
  
  - [x] 2.2 Update modal HTML with static text
    - Change modal text to always show "Status will change to 'Waiting for EPU Approval'"
    - Remove any placeholders or dynamic text elements
    - Ensure modal styling is consistent with other modals
    - _Requirements: 1.4, 8.1, 8.2, 8.4_
  
  - [ ]* 2.3 Write unit test for modal text consistency
    - Test modal always shows same text regardless of Pre-Project status
    - _Requirements: 8.1, 8.2_

- [x] 3. Remove "Waiting for Approver 2" status badge from Pre-Project list
  - [x] 3.1 Update status badge display logic in Blade view
    - Remove conditional block for "Waiting for Approver 2" status
    - Ensure only valid Pre-Project statuses are displayed
    - _Requirements: 1.3, 9.2_
  
  - [x] 3.2 Update action buttons logic
    - Show Approve/Reject buttons only for "Waiting for Approver 1" status
    - Show Submit to EPU button only for "Waiting for EPU Approval" status
    - Hide Edit/Delete buttons for statuses after "Waiting for Complete Form"
    - _Requirements: 9.3, 9.4_
  
  - [ ]* 3.3 Write unit test for status badge display
    - Test "Waiting for Approver 2" badge never appears
    - Test all valid status badges appear correctly
    - _Requirements: 9.1, 9.2_

- [x] 4. Update completeness percentage display conditions
  - [x] 4.1 Modify completeness column logic in Blade view
    - Show completeness only for "Waiting for Complete Form" and "Waiting for EPU Approval"
    - Show "N/A" for other statuses including "Waiting for Approver 1"
    - _Requirements: 10.1, 10.2, 10.3_
  
  - [ ]* 4.2 Write unit test for completeness display conditions
    - Test completeness shown for correct statuses
    - Test "N/A" shown for other statuses
    - _Requirements: 10.1, 10.2, 10.3_

- [x] 5. Implement NOC budget full allocation validation
  - [x] 5.1 Add updateBudgetSummary() JavaScript function
    - Calculate Total NOC Budget (sum of all Kos Asal)
    - Calculate Total Allocated Budget (sum of all Kos Baru)
    - Calculate Remaining Budget (Total NOC Budget - Total Allocated Budget)
    - Update budget summary display with formatted values
    - Update remaining budget color (red if < 0, black if >= 0)
    - Call validation function to update button state
    - _Requirements: 3.4, 3.5, 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [x] 5.2 Add formatCurrency() helper function
    - Format numbers with "RM" prefix
    - Format with two decimal places
    - Add thousand separators
    - _Requirements: 7.5_
  
  - [ ]* 5.3 Write unit test for budget calculation
    - Test Total NOC Budget calculation
    - Test Total Allocated Budget calculation
    - Test Remaining Budget calculation
    - _Requirements: 3.4, 7.1_

- [x] 6. Implement NOC empty row detection
  - [x] 6.1 Add checkForEmptyRows() JavaScript function
    - Skip empty state row (row with colspan="10")
    - Loop through all actual project rows in table
    - Check if New Cost (Kos Baru) value is 0 or empty string
    - Return true if any empty rows found, false otherwise
    - Only check rows that have .kos-baru-input field
    - _Requirements: 4.1, 4.6_
  
  - [x] 6.2 Fix empty row detection logic
    - Check for empty state row first before validating
    - Validate both value === 0 AND value.trim() === ''
    - Ensure validation only runs on actual project rows
    - _Requirements: 4.1_
  
  - [ ]* 6.3 Write unit test for empty row detection
    - Test detection of rows with no New Cost
    - Test detection passes when all rows have New Cost
    - Test detection ignores empty state row
    - _Requirements: 4.1_

- [x] 7. Implement NOC validation and button state management
  - [x] 7.1 Add validateBudgetAndUpdateButton() JavaScript function
    - Hide all validation messages initially
    - Check for over budget (remaining < 0) → show error, disable button
    - Check for empty rows → show warning, disable button
    - Check for remaining budget (remaining > 0) → show info, disable button
    - If all validations pass (remaining = 0 AND no empty rows) → enable button
    - Implement priority-based message display
    - _Requirements: 3.1, 3.2, 3.3, 4.2, 4.3, 5.1, 5.2, 6.1, 6.2, 6.3_
  
  - [ ]* 7.2 Write unit test for validation priority
    - Test over budget takes priority over other validations
    - Test empty rows takes priority over remaining budget
    - Test only one message shown at a time
    - _Requirements: 6.1, 6.2, 6.3_

- [x] 8. Add NOC validation message HTML
  - [x] 8.1 Add Budget Exceeded warning message
    - Red border-left, error icon
    - Message: "Budget Exceeded"
    - Guidance: "Total allocated budget exceeds the NOC budget. Please adjust the New Cost values."
    - Initially hidden (display: none)
    - _Requirements: 5.1, 5.2, 6.5_
  
  - [x] 8.2 Add Empty Rows Detected warning message
    - Red border-left, warning icon
    - Message: "Empty Rows Detected"
    - Guidance: "Please delete empty rows (rows without New Cost) before creating NOC."
    - Initially hidden (display: none)
    - _Requirements: 4.3, 4.4, 6.5_
  
  - [x] 8.3 Add Budget Not Fully Allocated info message
    - Yellow border-left, info icon
    - Message: "Budget Not Fully Allocated"
    - Guidance: "Please allocate all remaining budget before creating NOC."
    - Initially hidden (display: none)
    - _Requirements: 3.2, 6.5_
  
  - [ ]* 8.4 Write unit test for message display
    - Test correct message shown for each validation state
    - Test message styling is consistent
    - _Requirements: 6.5_

- [x] 9. Add event listeners for real-time validation
  - [x] 9.1 Add input event listeners to New Cost fields
    - Trigger updateBudgetSummary() on input change
    - Ensure all dynamically added rows also have listeners
    - _Requirements: 3.5, 7.4_
  
  - [x] 9.2 Add event listeners to delete buttons
    - Trigger updateBudgetSummary() after row deletion
    - Ensure validation runs after DOM update
    - _Requirements: 4.6_
  
  - [x] 9.3 Add DOMContentLoaded event listener
    - Trigger updateBudgetSummary() on page load
    - Initialize button state based on initial data
    - _Requirements: 3.5, 7.4_
  
  - [ ]* 9.4 Write integration test for real-time updates
    - Test budget updates on input change
    - Test validation updates on row deletion
    - Test button state changes correctly
    - _Requirements: 3.5, 4.6, 7.4_

- [ ] 10. Integration testing for Pre-Project approval workflow
  - [ ]* 10.1 Write integration test for complete approval workflow
    - Create Pre-Project with status "Waiting for Approver 1"
    - Test unauthorized user cannot approve
    - Test authorized approver can approve
    - Verify status changes to "Waiting for EPU Approval" (not "Waiting for Approver 2")
    - Verify approval timestamp and user ID recorded
    - Verify modal shows correct static text
    - _Requirements: 1.1, 1.2, 1.4, 8.1, 8.2_

- [ ] 11. Integration testing for NOC budget validation
  - [ ]* 11.1 Write integration test for complete validation workflow
    - Open NOC creation page
    - Import projects → verify Total NOC Budget calculated
    - Enter New Cost values → verify Total Allocated Budget calculated
    - Test over budget scenario → verify error shown, button disabled
    - Test empty rows scenario → verify warning shown, button disabled
    - Test remaining budget scenario → verify info shown, button disabled
    - Allocate all budget and delete empty rows → verify button enabled
    - Verify only one validation message shown at a time
    - _Requirements: 3.1, 3.2, 3.3, 4.2, 4.3, 5.1, 5.2, 6.1, 6.2, 6.3_

- [ ] 12. Browser testing for JavaScript functionality
  - [ ]* 12.1 Test budget calculation in browser
    - Verify calculations are accurate
    - Verify currency formatting is correct
    - Verify color changes work correctly
    - Test in multiple browsers (Chrome, Firefox, Safari, Edge)
  
  - [ ]* 12.2 Test empty row detection in browser
    - Verify empty rows are detected correctly
    - Verify detection updates when rows are deleted
    - Test in multiple browsers
  
  - [ ]* 12.3 Test validation message priority in browser
    - Verify correct message shown for each state
    - Verify message switching works correctly
    - Verify button state changes correctly
    - Test in multiple browsers

- [ ] 13. Final verification and documentation
  - [ ]* 13.1 Verify all requirements are met
    - Review requirements document
    - Check each acceptance criterion
    - Document any deviations or limitations
  
  - [ ]* 13.2 Update user documentation
    - Document Pre-Project approval workflow
    - Document NOC budget validation rules
    - Provide examples and screenshots
  
  - [ ]* 13.3 Create summary document
    - List all changes made
    - Document files modified
    - Provide before/after comparisons

## Notes

- Tasks marked with `[x]` are completed
- Tasks marked with `[ ]*` are optional testing tasks
- All core functionality has been implemented and is working
- Testing tasks are recommended but not required for MVP
- No database migrations required - all fields already exist
- Changes are backward compatible with existing data
- JavaScript validation is client-side only - server-side validation recommended for production

## Files Modified

### Pre-Project Approval Workflow
- `app/Http/Controllers/Pages/PageController.php` - Updated preProjectApprove() and preProjectReject() methods
- `resources/views/pages/pre-project.blade.php` - Updated approval modal HTML and JavaScript

### NOC Budget Validation
- `resources/views/pages/project-noc-create.blade.php` - Added budget calculation, validation, and message display logic

## Summary of Changes

### Pre-Project Approval
- ✅ Approval now skips "Waiting for Approver 2" status
- ✅ Status changes directly from "Waiting for Approver 1" to "Waiting for EPU Approval"
- ✅ Approval modal shows static text (no dynamic changes)
- ✅ "Waiting for Approver 2" status badge removed from list view
- ✅ Completeness percentage only shown for relevant statuses

### NOC Budget Validation
- ✅ Create NOC button disabled when remaining budget > 0
- ✅ Create NOC button disabled when empty rows detected
- ✅ Create NOC button disabled when over budget
- ✅ Create NOC button enabled only when remaining = 0 AND no empty rows
- ✅ Real-time budget calculation and validation
- ✅ Priority-based validation message display
- ✅ Color-coded remaining budget display

