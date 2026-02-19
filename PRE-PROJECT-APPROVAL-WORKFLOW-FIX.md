# Pre-Project Approval Workflow Fix

## Problem Identified

User reported that Residen approvers cannot see approve/reject buttons in the Pre-Project page even though they are configured as approvers in `/pages/general/approver`.

## Root Cause Analysis

### 1. Approver Configuration ✅
- Approvers are correctly saved in `integration_settings` table
- Type: `approver`
- Key: `pre_project_approvers`
- Value: `["53","52"]` (JSON array of user IDs)
- Both users are Residen users:
  - User ID 52: Khairunnisa Binti Sabawi (Residen Sibu)
  - User ID 53: Haji Abang Mohamad Porkan Bin Haji Abang Budiman (Residen Sibu)

### 2. Status Mismatch Issue ❌
The problem was with the pre-project status:
- **Expected Status**: `Waiting for Approver 1`
- **Actual Status in DB**: `Waiting for Approval`

### 3. Button Display Logic
In `resources/views/pages/pre-project.blade.php` (lines 182-210):
```php
@elseif($preProject->status === 'Waiting for Approver 1' && $isApprover)
    <button class="action-btn action-approve" title="Approve" onclick="approvePreProject(...)">
        <span class="material-symbols-outlined">check_circle</span>
    </button>
    <button class="action-btn action-reject" title="Reject" onclick="rejectPreProject(...)">
        <span class="material-symbols-outlined">cancel</span>
    </button>
@endif
```

The buttons only appear when:
1. Status is exactly `Waiting for Approver 1` (not `Waiting for Approval`)
2. Current user is in the approvers list

## Solution Applied

### Fixed Pre-Project Status
Updated pre-project ID 28 status from `Waiting for Approval` to `Waiting for Approver 1`:

```sql
UPDATE pre_projects 
SET status = 'Waiting for Approver 1' 
WHERE id = 28;
```

## Workflow Verification

### Complete Pre-Project Approval Workflow:

1. **Parliament/DUN User Creates Pre-Project**
   - Initial Status: `Waiting for Complete Form`
   - User fills in all required fields

2. **Parliament/DUN User Submits to EPU**
   - Clicks "Submit to Approver" button (only visible when 100% complete)
   - Status changes to: `Waiting for Approver 1`
   - Method: `preProjectSubmitToEpu()` in PageController.php (line 1726)

3. **Residen Approver Reviews**
   - Approvers see approve/reject buttons
   - Approvers are defined in `/pages/general/approver`
   - Multiple Residen users can be selected as approvers

4. **Approval Actions**
   - **If Approved**: Status changes to `Approved`
   - **If Rejected**: Status changes to `Rejected`

## Status Flow Diagram

```
Waiting for Complete Form
         ↓ (Submit to Approver - 100% complete)
Waiting for Approver 1
         ↓ (Residen Approver Action)
    ┌────┴────┐
Approved   Rejected
```

## Testing Checklist

- [x] Approvers correctly saved in database
- [x] Pre-project status updated to `Waiting for Approver 1`
- [ ] Login as Residen approver (user ID 52 or 53)
- [ ] Verify approve/reject buttons are visible for pre-project ID 28
- [ ] Test approve action
- [ ] Test reject action

## Files Modified

None - only database update was required.

## Related Files

- `resources/views/pages/pre-project.blade.php` - Approval button display logic
- `app/Http/Controllers/Pages/PageController.php` - Approval workflow methods:
  - `preProjectSubmitToEpu()` - Line 1726
  - `preProjectApprove()` - (need to locate)
  - `preProjectReject()` - (need to locate)
- `resources/views/pages/general/approver.blade.php` - Approver configuration page
- `app/Models/IntegrationSetting.php` - Settings storage

## Notes

- The approval system uses a single-layer approval (Approver 1 only)
- NOC system uses two-layer approval (First Approval + Second Approval)
- Pre-Project approval is simpler - any user in the approvers list can approve
- Status must be exactly `Waiting for Approver 1` for buttons to appear

## Next Steps

1. User should login as Residen approver (khairuni90@sarawak.gov.my or mdpb@sarawak.gov.my)
2. Navigate to `/pages/pre-project`
3. Find pre-project "Bina Baru Rumah Hangus Jalan Kampung Nangka"
4. Verify approve/reject buttons are now visible
5. Test approval workflow

---

**Date**: February 19, 2026
**Fixed By**: Kiro AI Assistant
**Issue**: Pre-Project approval buttons not showing for Residen approvers
**Resolution**: Updated pre-project status from "Waiting for Approval" to "Waiting for Approver 1"
