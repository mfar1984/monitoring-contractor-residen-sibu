# Pre-Project Approval Workflow Implementation Summary

## Overview
Implemented a multi-stage approval workflow for Pre-Projects with status progression from creation to EPU approval and final transfer.

## Workflow Status Flow

### Complete Status Progression:
1. **Waiting for Approval** (Initial) - Created by Parliament/DUN users
2. **Waiting for EPU Approval** - Approved by tagged approvers, waiting for EPU to provide Project Number from RTP System
3. **Approved** - Transferred to Project with EPU Project Number

### Additional Statuses:
- **NOC** - Pre-Project is included in a NOC document
- **Active** - Legacy status (no longer used for new projects)

## Key Clarification

**"Waiting for EPU Approval"** means:
- Pre-Project has been approved by internal approvers
- Now waiting for EPU to provide Project Number from external RTP System
- User must manually enter the Project Number and Year received from EPU
- Only after entering EPU Project Number can the Pre-Project be transferred
- Transfer action changes status to "Approved"

## Changes Made

### 1. Database Migration
- **File**: `database/migrations/2026_02_15_210000_add_approval_statuses_to_pre_projects_table.php`
- Updated `status` ENUM to include:
  - `Waiting for Approval`
  - `Waiting for EPU Approval`
  - `Approved`
  - `NOC`
  - `Active` (legacy)
- Changed default status to `Waiting for Approval`

### 2. Controller Updates
- **File**: `app/Http/Controllers/Pages/PageController.php`

#### Modified Methods:
- `preProjectStore()`: Changed initial status from `Active` to `Waiting for Approval`

#### New Methods:
- `preProjectApprove($id)`: Approve Pre-Project
  - Validates user is authorized approver
  - Checks Pre-Project status is `Waiting for Approval`
  - Updates status to `Waiting for EPU Approval`
  - Returns success message
  
- `preProjectReject($id)`: Reject Pre-Project
  - Validates user is authorized approver
  - Checks Pre-Project status is `Waiting for Approval`
  - Deletes the Pre-Project permanently
  - Returns success message

### 3. Routes
- **File**: `routes/web.php`
- Added routes:
  - `POST /pages/pre-project/{id}/approve` → `preProjectApprove()`
  - `POST /pages/pre-project/{id}/reject` → `preProjectReject()`

### 4. Pre-Project List View
- **File**: `resources/views/pages/pre-project.blade.php`

#### Visual Updates:
- **Row Highlighting**:
  - Yellow background (`#fff3cd`) for `Waiting for Approval`
  - Blue background (`#cce5ff`) for `Waiting for EPU Approval`
  - Red background (`#ffe6e6`) for `NOC`
  
- **Status Badges**:
  - Yellow badge for `Waiting for Approval`
  - Blue badge for `Waiting for EPU Approval`
  - Green badge for `Approved`
  - Red badge for `NOC`

#### Action Buttons Logic:
- **For Approvers** (when status is `Waiting for Approval`):
  - Green Approve button (check_circle icon)
  - Red Reject button (cancel icon)
  
- **For Non-Approvers** (when status is `Waiting for Approval` or `Waiting for EPU Approval`):
  - Edit button (enabled)
  - Delete button (enabled)
  
- **For Approved/NOC Status**:
  - Edit button (disabled, greyed out)
  - Delete button (disabled, greyed out)

#### JavaScript Functions:
- `approvePreProject(id, name)`: Submit approval form with confirmation
- `rejectPreProject(id, name)`: Submit rejection form with warning confirmation

## Authorization

### Pre-Project Approvers
- Configured in **General Settings > Approver** tab
- Multiple approvers can be selected (1st layer approval)
- Stored in `integration_settings` table:
  - Type: `approver`
  - Key: `pre_project_approvers`
  - Format: JSON array of user IDs

### Approval Logic
- Only users in the approver list can see Approve/Reject buttons
- Approval changes status to `Waiting for EPU Approval`
- Rejection deletes the Pre-Project permanently
- Only Pre-Projects with status `Waiting for Approval` can be approved/rejected

## User Experience

### Creating Pre-Project:
1. Parliament/DUN user creates Pre-Project
2. Status automatically set to `Waiting for Approval`
3. Row highlighted in yellow
4. Edit/Delete buttons available to creator

### Approval Process:
1. Approver sees Approve/Reject buttons (green/red)
2. Clicking Approve:
   - Confirmation dialog appears
   - Status changes to `Waiting for EPU Approval`
   - Row highlighted in blue
   - Success message displayed
3. Clicking Reject:
   - Warning dialog appears (mentions deletion)
   - Pre-Project is deleted permanently
   - Success message displayed

### Transfer to Project:
1. Pre-Project with status `Waiting for EPU Approval` can be transferred
2. After transfer, status changes to `Approved`
3. Row no longer highlighted
4. Edit/Delete buttons disabled

## Status Color Coding

| Status | Background Color | Badge Color | Badge Text Color |
|--------|-----------------|-------------|------------------|
| Waiting for Approval | #fff3cd (yellow) | #ffc107 | #856404 (dark yellow) |
| Waiting for EPU Approval | #cce5ff (blue) | #17a2b8 | white |
| Approved | none | #28a745 (green) | white |
| NOC | #ffe6e6 (red) | #dc3545 (red) | white |

## Testing Checklist
- [x] Migration runs successfully
- [x] New Pre-Projects created with `Waiting for Approval` status
- [x] Approvers can see Approve/Reject buttons
- [x] Non-approvers cannot see Approve/Reject buttons
- [x] Approve button changes status to `Waiting for EPU Approval`
- [x] Reject button deletes Pre-Project
- [x] Status badges display correctly
- [x] Row highlighting works for all statuses
- [x] Edit/Delete buttons disabled for Approved/NOC status
- [x] No PHP/Blade syntax errors

## Future Enhancements
- Add approval history tracking (who approved, when)
- Add remarks/comments for approval/rejection
- Email notifications for approval actions
- Approval dashboard for approvers

## Status
✅ **COMPLETE** - All changes implemented and tested successfully.

## Date
February 15, 2026
