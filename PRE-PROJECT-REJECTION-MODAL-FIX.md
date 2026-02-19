# Pre-Project Rejection Modal Implementation

## Date: February 17, 2026

## Problem Report
User melaporkan: "kenapa approver nak reject. dia jadi macam ni. Sepatutnya reject akan keluar popup note selepas itu kalau submit reject, ia akan revert balik untuk id parliament atau duns untuk edit lagi dan submit lagi sekali."

## Current Issue

**Before Fix**:
- Reject button shows simple browser `confirm()` dialog
- No way to enter rejection reason/remarks
- No proper rejection tracking
- Message says "will DELETE permanently" (misleading - actually reverts status)

## Solution Implemented

### 1. Database Migration
**File**: `database/migrations/2026_02_17_114457_add_rejection_fields_to_pre_projects_table.php`

Added 3 new fields to track rejection:
```php
$table->text('rejection_remarks')->nullable();
$table->foreignId('rejected_by')->nullable()->constrained('users');
$table->timestamp('rejected_at')->nullable();
```

**Migration Status**: ✅ Executed successfully

### 2. Updated PreProject Model
**File**: `app/Models/PreProject.php`

Added rejection fields to fillable array:
```php
'rejection_remarks',
'rejected_by',
'rejected_at',
```

### 3. Updated Controller Method
**File**: `app/Http/Controllers/Pages/PageController.php`

**Method**: `preProjectReject(Request $request, $id)`

**Changes**:
- Added `Request $request` parameter to receive form data
- Added validation for `rejection_remarks`:
  - Required field
  - Minimum 10 characters
  - Maximum 500 characters
- Store rejection data:
  - `rejection_remarks`: User's reason for rejection
  - `rejected_by`: ID of user who rejected
  - `rejected_at`: Timestamp of rejection
- Revert status to "Waiting for Complete Form"
- Clear all approval data

**Validation Rules**:
```php
$request->validate([
    'rejection_remarks' => 'required|string|min:10|max:500'
], [
    'rejection_remarks.required' => 'Please provide a reason for rejection',
    'rejection_remarks.min' => 'Rejection reason must be at least 10 characters',
    'rejection_remarks.max' => 'Rejection reason cannot exceed 500 characters'
]);
```

**Update Logic**:
```php
$preProject->update([
    'status' => 'Waiting for Complete Form',
    'rejection_remarks' => $request->rejection_remarks,
    'rejected_by' => $user->id,
    'rejected_at' => now(),
    'first_approver_id' => null,
    'first_approved_at' => null,
    'second_approver_id' => null,
    'second_approved_at' => null,
    'submitted_to_epu_at' => null,
    'submitted_to_epu_by' => null
]);
```

### 4. Frontend - Rejection Modal
**File**: `resources/views/pages/pre-project.blade.php`

#### Modal HTML Structure
Added new modal with:
- **Header**: Red background with cancel icon
- **Warning Icon**: Large red block icon
- **Project Name Display**: Shows which project is being rejected
- **Info Box**: Blue gradient box explaining what happens on rejection
- **Rejection Remarks Textarea**: 
  - Required field
  - Minimum 10 characters
  - Placeholder text for guidance
  - Character count hint
- **Submit Button**: 
  - Disabled by default
  - Enabled only when remarks >= 10 characters
  - Red color to indicate destructive action

#### JavaScript Functions

**`rejectPreProject(id, name)`**:
```javascript
function rejectPreProject(id, name) {
    // Store ID and name
    window.rejectPreProjectId = id;
    window.rejectPreProjectName = name;
    
    // Update modal content
    document.getElementById('rejectProjectName').textContent = name;
    document.getElementById('rejectForm').action = '/pages/pre-project/' + id + '/reject';
    document.getElementById('rejection_remarks').value = '';
    document.getElementById('rejectError').style.display = 'none';
    document.getElementById('rejectSubmitBtn').disabled = true;
    
    // Show modal
    document.getElementById('rejectModal').classList.add('show');
    
    // Focus on textarea
    setTimeout(() => {
        document.getElementById('rejection_remarks').focus();
    }, 100);
}
```

**`closeRejectModal()`**:
```javascript
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('show');
    document.getElementById('rejection_remarks').value = '';
    document.getElementById('rejectError').style.display = 'none';
}
```

**`validateRejectForm()`**:
```javascript
function validateRejectForm() {
    const remarks = document.getElementById('rejection_remarks').value.trim();
    const submitBtn = document.getElementById('rejectSubmitBtn');
    
    if (remarks.length < 10) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        return false;
    }
    
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    submitBtn.style.cursor = 'pointer';
    return true;
}
```

**Event Listener for Click Outside**:
```javascript
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
```

## User Flow

### Before (Wrong ❌):
1. Approver clicks Reject button
2. Browser confirm dialog appears: "Are you sure... will DELETE permanently"
3. Click OK → Pre-Project rejected (no reason recorded)
4. Misleading message about deletion

### After (Correct ✅):
1. Approver clicks Reject button (❌ icon)
2. **Rejection Modal appears** with:
   - Project name display
   - Info box explaining what happens
   - Textarea for rejection reason
   - Submit button (disabled initially)
3. Approver types rejection reason (min 10 characters)
4. Submit button enables automatically
5. Click "Reject Pre-Project" button
6. System records:
   - Rejection remarks
   - Who rejected (user ID)
   - When rejected (timestamp)
7. Status changes to "Waiting for Complete Form"
8. Parliament/DUN user can edit and resubmit

## Modal Design Features

### Visual Hierarchy
- **Red header**: Indicates destructive action
- **Large warning icon**: 64px red block icon in circle
- **Blue info box**: Explains consequences clearly
- **Disabled submit button**: Prevents accidental submission

### User Experience
- **Auto-focus**: Textarea gets focus when modal opens
- **Real-time validation**: Submit button enables as user types
- **Character hint**: Shows minimum requirement (10 chars)
- **Click outside to close**: Modal closes when clicking overlay
- **Clear error messages**: Validation errors shown inline

### Accessibility
- Required field indicator (red asterisk)
- Placeholder text for guidance
- Visual feedback on button state
- Clear action labels

## Testing Checklist

### 1. Open Rejection Modal
- [x] Click Reject button on Pre-Project with "Waiting for Approver 1" status
- [x] Modal appears with correct project name
- [x] Submit button is disabled
- [x] Textarea is focused

### 2. Validation
- [x] Type less than 10 characters → Submit button stays disabled
- [x] Type 10+ characters → Submit button enables
- [x] Clear textarea → Submit button disables again

### 3. Submit Rejection
- [x] Fill rejection reason (min 10 chars)
- [x] Click "Reject Pre-Project" button
- [x] Success message appears
- [x] Status changes to "Waiting for Complete Form"

### 4. Database Verification
```bash
php artisan tinker --execute="
\$p = \App\Models\PreProject::find(26);
echo 'Status: ' . \$p->status . PHP_EOL;
echo 'Rejection Remarks: ' . \$p->rejection_remarks . PHP_EOL;
echo 'Rejected By: ' . \$p->rejected_by . PHP_EOL;
echo 'Rejected At: ' . \$p->rejected_at . PHP_EOL;
"
```

Expected output:
```
Status: Waiting for Complete Form
Rejection Remarks: [User's rejection reason]
Rejected By: [Approver user ID]
Rejected At: [Timestamp]
```

### 5. Parliament User Can Edit
- [x] Login as Parliament/DUN user
- [x] See rejected Pre-Project with "Waiting for Complete Form" status
- [x] Edit button is enabled
- [x] Can edit and resubmit

## Files Modified

1. ✅ `database/migrations/2026_02_17_114457_add_rejection_fields_to_pre_projects_table.php` - New migration
2. ✅ `app/Models/PreProject.php` - Added rejection fields to fillable
3. ✅ `app/Http/Controllers/Pages/PageController.php` - Updated preProjectReject method
4. ✅ `resources/views/pages/pre-project.blade.php` - Added rejection modal and JavaScript

## Benefits

### For Approvers
- Can provide detailed rejection reasons
- Clear explanation of what happens on rejection
- Prevents accidental rejections (disabled button until reason entered)

### For Parliament/DUN Users
- Understand why Pre-Project was rejected
- Can see rejection remarks (future enhancement: display in view modal)
- Can edit and resubmit with corrections

### For System
- Complete audit trail of rejections
- Track who rejected and when
- Store rejection reasons for future reference

## Future Enhancements (Optional)

1. **Display Rejection History**:
   - Show rejection remarks in View modal
   - Show who rejected and when
   - Show rejection history if rejected multiple times

2. **Email Notification**:
   - Send email to Parliament/DUN user when rejected
   - Include rejection remarks in email

3. **Rejection Statistics**:
   - Track most common rejection reasons
   - Identify Pre-Projects with multiple rejections

## Summary

**Problem**: Reject button used simple confirm dialog, no way to enter rejection reason, misleading message.

**Solution**: Implemented proper rejection modal with:
- Textarea for rejection remarks (min 10 chars)
- Real-time validation
- Clear info about consequences
- Database tracking of rejection data

**Result**: Approvers can now provide detailed rejection reasons, and Parliament/DUN users can understand why their Pre-Project was rejected and make necessary corrections.

**Status**: COMPLETE ✅

---

**Last Updated**: February 17, 2026
**Feature**: Rejection Modal with Remarks
**Impact**: Improved approval workflow transparency

