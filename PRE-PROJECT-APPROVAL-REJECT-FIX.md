# Pre-Project Approval & Reject Workflow Fix

## Issue Summary

When approver (Residen user) tried to reject a pre-project with status "Waiting for Approval", the system did not respond because the controller was checking for status "Waiting for Approver 1" only.

## Root Cause

The system had inconsistent status naming:
- Pre-projects created by Parliament/DUN users had status: **"Waiting for Approval"**
- Controller methods checked for status: **"Waiting for Approver 1"**

This mismatch caused:
1. Reject button to appear (blade logic was fixed)
2. But reject action to fail silently (controller validation failed)

## Permanent Solution Implemented

### 1. Controller Methods Updated

**File:** `app/Http/Controllers/Pages/PageController.php`

#### preProjectReject() Method
```php
// BEFORE (Only checked for one status)
if ($preProject->status !== 'Waiting for Approver 1') {
    return redirect()->back()->with('error', 'This Pre-Project cannot be rejected at this stage');
}

// AFTER (Checks for both statuses)
if (!in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1'])) {
    return redirect()->back()->with('error', 'This Pre-Project cannot be rejected at this stage');
}
```

#### preProjectApprove() Method
```php
// BEFORE (Only checked for one status)
if ($preProject->status === 'Waiting for Approver 1') {
    // Approve logic
}

// AFTER (Checks for both statuses)
if (in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1'])) {
    // Approve logic
}
```

### 2. Blade Template Updated

**File:** `resources/views/pages/pre-project.blade.php`

#### Action Buttons Logic
```blade
{{-- Support both status variations --}}
@elseif(in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1']) && $isPreProjectApprover)
    <button class="action-btn action-approve" title="Approve">...</button>
    <button class="action-btn action-reject" title="Reject">...</button>
```

#### Status Badge Display
```blade
@elseif(in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1']))
    <span class="status-badge" style="background-color: #ffc107; color: #856404;">Waiting for Approval</span>
```

### 3. Controller - Approver Info Passed to View

**File:** `app/Http/Controllers/Pages/PageController.php`

```php
public function preProject(): View
{
    // ... existing code ...
    
    // Get pre-project approvers
    $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
    $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
    $isPreProjectApprover = in_array($user->id, $preProjectApprovers);
    
    return view('pages.pre-project', compact(
        // ... other variables ...
        'isPreProjectApprover'
    ));
}
```

## Workflow After Fix

### Reject Workflow
1. **Approver clicks Reject button**
   - Modal opens with rejection form
   - Requires minimum 10 characters reason

2. **Approver submits rejection**
   - Controller validates:
     - User is authorized approver ✅
     - Status is "Waiting for Approval" OR "Waiting for Approver 1" ✅
     - Rejection remarks provided ✅
   
3. **System updates pre-project**
   ```php
   status: 'Waiting for Complete Form'
   rejection_remarks: 'User provided reason'
   rejected_by: approver_user_id
   rejected_at: current_timestamp
   ```

4. **Parliament/DUN user can now:**
   - See status "Waiting for Complete Form"
   - Edit button enabled
   - Delete button enabled
   - Make changes and resubmit

### Approve Workflow
1. **Approver clicks Approve button**
   - Modal opens with optional approval remarks

2. **Approver submits approval**
   - Controller validates:
     - User is authorized approver ✅
     - Status is "Waiting for Approval" OR "Waiting for Approver 1" ✅
   
3. **System updates pre-project**
   ```php
   status: 'Waiting for EPU Approval'
   first_approver_id: approver_user_id
   first_approved_at: current_timestamp
   first_approval_remarks: 'Optional remarks'
   ```

## Database Schema

### Required Columns (Already Exist)
```sql
-- Rejection fields
rejection_remarks TEXT NULL
rejected_by BIGINT UNSIGNED NULL (FK to users)
rejected_at TIMESTAMP NULL

-- Approval fields
first_approver_id BIGINT UNSIGNED NULL (FK to users)
first_approved_at TIMESTAMP NULL
first_approval_remarks TEXT NULL
second_approver_id BIGINT UNSIGNED NULL (FK to users)
second_approved_at TIMESTAMP NULL
second_approval_remarks TEXT NULL
```

### Migration Status
✅ `2026_02_17_114457_add_rejection_fields_to_pre_projects_table` - Ran

## Testing Checklist

### ✅ Reject Functionality
- [x] Reject button appears for approvers
- [x] Reject modal opens correctly
- [x] Validation requires minimum 10 characters
- [x] Status changes to "Waiting for Complete Form"
- [x] Rejection remarks saved
- [x] Rejected_by and rejected_at recorded
- [x] Parliament user can edit after rejection

### ✅ Approve Functionality
- [x] Approve button appears for approvers
- [x] Approve modal opens correctly
- [x] Status changes to "Waiting for EPU Approval"
- [x] Approval details recorded
- [x] Success message displayed

### ✅ Status Handling
- [x] "Waiting for Approval" status supported
- [x] "Waiting for Approver 1" status supported
- [x] Status badge displays correctly
- [x] Row background color correct
- [x] Completeness percentage shows for approval statuses

## Why This Fix is Permanent

### 1. **Flexible Status Handling**
- Uses `in_array()` to check multiple status variations
- Future-proof if status naming changes
- No hardcoded single status checks

### 2. **Centralized Logic**
- Approver check in controller (single source of truth)
- Passed to view as variable
- No duplicate logic in blade files

### 3. **Database-Driven**
- Approver settings from `integration_settings` table
- Dynamic updates when admin changes approvers
- No hardcoded user IDs

### 4. **Consistent Validation**
- Both approve and reject methods use same status check
- Same approver authorization logic
- Consistent error messages

### 5. **Proper Error Handling**
- Validation errors displayed to user
- Success messages on completion
- Redirect back with feedback

## Configuration

### Approver Settings Location
**URL:** `http://localhost:8000/pages/general/approver`

**Database:**
```sql
SELECT * FROM integration_settings 
WHERE type = 'approver' 
AND key = 'pre_project_approvers';

-- Value format: ["53","52"] (JSON array of user IDs)
```

### How to Add/Remove Approvers
1. Login as admin
2. Go to System Settings → General → Approver
3. Select/deselect users in "Pre-Project Approvers" field
4. Click Save
5. Changes apply immediately (no cache clear needed)

## Related Files Modified

1. ✅ `app/Http/Controllers/Pages/PageController.php`
   - `preProject()` method - Added approver info
   - `preProjectApprove()` method - Fixed status check
   - `preProjectReject()` method - Fixed status check

2. ✅ `resources/views/pages/pre-project.blade.php`
   - Action buttons logic - Support both statuses
   - Status badge display - Unified display
   - Row background color - Support both statuses
   - Completeness check - Include both statuses

## Future Improvements (Optional)

### 1. Status Constants
Create a PreProjectStatus enum/class:
```php
class PreProjectStatus {
    const WAITING_COMPLETE = 'Waiting for Complete Form';
    const WAITING_APPROVAL = 'Waiting for Approval';
    const WAITING_APPROVER_1 = 'Waiting for Approver 1';
    const WAITING_EPU = 'Waiting for EPU Approval';
    const APPROVED = 'Approved';
    const REJECTED = 'Rejected';
}
```

### 2. Notification System
Send email/SMS to Parliament user when rejected:
```php
// In preProjectReject() method
Mail::to($preProject->creator->email)->send(
    new PreProjectRejectedMail($preProject)
);
```

### 3. Rejection History
Track all rejection attempts:
```php
// Create pre_project_rejections table
id, pre_project_id, rejected_by, rejection_remarks, rejected_at
```

## Troubleshooting

### Issue: Reject button not appearing
**Check:**
1. User ID in approvers list: `SELECT * FROM integration_settings WHERE key = 'pre_project_approvers'`
2. Pre-project status: Should be "Waiting for Approval" or "Waiting for Approver 1"
3. Clear cache: `php artisan view:clear && php artisan cache:clear`

### Issue: Reject action fails silently
**Check:**
1. Browser console for JavaScript errors
2. Laravel logs: `storage/logs/laravel.log`
3. Network tab: Check POST request to `/pages/pre-project/{id}/reject`
4. Validation errors: Check if rejection_remarks is provided

### Issue: Status not changing after reject
**Check:**
1. Database columns exist: `DESCRIBE pre_projects`
2. Migration ran: `php artisan migrate:status | grep rejection`
3. User has permission: Check if user ID in approvers list

## Summary

This fix ensures that the Pre-Project approval and rejection workflow functions correctly regardless of whether the status is "Waiting for Approval" or "Waiting for Approver 1". The solution is permanent because it:

1. ✅ Handles multiple status variations
2. ✅ Uses centralized, database-driven logic
3. ✅ Provides proper validation and error handling
4. ✅ Maintains consistency across approve and reject flows
5. ✅ Requires no manual intervention after deployment

**Status:** ✅ RESOLVED - Permanent Fix Implemented
**Date:** February 19, 2026
**Tested:** ✅ Approve and Reject workflows verified
