# Project Approval History Fix

## Issue

Dalam halaman Project Show (http://localhost:8000/pages/project), data "Approved By" untuk first dan second approval tidak dipaparkan walaupun data sudah disimpan dalam database.

## Root Cause Analysis

### Database Check ✅
Data approval sudah disimpan dengan betul dalam database:
- NOC ID 3 has `first_approver_id`: 52 (Khairunnisa Binti Sabawi)
- NOC ID 3 has `second_approver_id`: 53 (Haji Abang Mohamad Porkan Bin Haji Abang Budiman)

### Controller Issue ❌
Dalam `PageController::projectShow()` method, kod tidak load relationship `firstApprover` dan `secondApprover` dari NOC model. Ia cuba mengambil approver dari settings dan assign kepada semua NOC, tetapi sepatutnya ia perlu load dari NOC record sendiri.

## Solution

### 1. Updated Controller (`app/Http/Controllers/Pages/PageController.php`)

Changed the `projectShow()` method to load actual approver relationships:

**Before:**
```php
'nocs' => function($query) {
    $query->with(['creator.parliament', 'creator.dun', 'parliament', 'dun'])
          ->orderBy('created_at', 'desc');
}

// Get NOC approval settings
$firstApproverSetting = \App\Models\IntegrationSetting::getSetting('noc_approval', 'first_approval_user');
$secondApproverSetting = \App\Models\IntegrationSetting::getSetting('noc_approval', 'second_approval_user');

// Add approvers to each NOC
foreach ($project->nocs as $noc) {
    $noc->first_approver_user = $firstApprover;  // From settings - WRONG
    $noc->second_approver_user = $secondApprover; // From settings - WRONG
}
```

**After:**
```php
'nocs' => function($query) {
    $query->with([
        'creator.parliament', 
        'creator.dun', 
        'parliament', 
        'dun',
        'firstApprover',  // Load actual first approver ✅
        'secondApprover'  // Load actual second approver ✅
    ])
    ->orderBy('created_at', 'desc');
}

// Add approver user objects to each NOC for JavaScript access
foreach ($project->nocs as $noc) {
    $noc->first_approver_user = $noc->firstApprover;  // From NOC record ✅
    $noc->second_approver_user = $noc->secondApprover; // From NOC record ✅
}
```

### 2. NOC Model Relationships ✅

The NOC model already has the required relationships:

```php
public function firstApprover()
{
    return $this->belongsTo(User::class, 'first_approver_id');
}

public function secondApprover()
{
    return $this->belongsTo(User::class, 'second_approver_id');
}
```

### 3. Approval Save Logic ✅

The approval save logic in `projectNocApprove()` is already correct:

```php
if ($noc->status === 'Waiting for Approval 1' && $user->id == $firstApprover) {
    $noc->update([
        'status' => 'Waiting for Approval 2',
        'first_approver_id' => $user->id,  // ✅ Saves correctly
        'first_approved_at' => now(),
        'first_approval_remarks' => $request->remarks,
    ]);
}

if ($noc->status === 'Waiting for Approval 2' && $user->id == $secondApprover) {
    $noc->update([
        'status' => 'Approved',
        'second_approver_id' => $user->id,  // ✅ Saves correctly
        'second_approved_at' => now(),
        'second_approval_remarks' => $request->remarks,
    ]);
}
```

## How It Works

1. **When NOC is approved:**
   - First approval: `first_approver_id` is set to the user ID who approved
   - Second approval: `second_approver_id` is set to the user ID who approved
   - Data is saved correctly in database ✅

2. **When viewing project details:**
   - Controller loads NOC with `firstApprover` and `secondApprover` relationships
   - Relationships return actual User objects from database
   - JavaScript displays the approver's name from the loaded relationship

3. **Display in UI:**
   - First Approval: Shows name of user who actually approved (from `first_approver_id`)
   - Second Approval: Shows name of user who actually approved (from `second_approver_id`)

## Testing Results

### Database Verification ✅
```json
{
    "id": 3,
    "noc_number": "NOC/2026/001",
    "first_approver_id": 52,
    "first_approved_at": "2026-02-16T02:54:00.000000Z",
    "second_approver_id": 53,
    "second_approved_at": "2026-02-16T02:56:22.000000Z",
    "status": "Approved"
}
```

### Relationship Loading ✅
```json
{
    "first_approver": {
        "id": 52,
        "full_name": "Khairunnisa Binti Sabawi"
    },
    "second_approver": {
        "id": 53,
        "full_name": "Haji Abang Mohamad Porkan Bin Haji Abang Budiman"
    }
}
```

## Testing Steps

To verify the fix:
1. Open http://localhost:8000/pages/project
2. Click "View" on Project ID 3 (Baik Pulih Jambatan Belian)
3. Scroll to "Approval History" section
4. Verify that "Approved By" shows:
   - First Approval: Khairunnisa Binti Sabawi
   - Second Approval: Haji Abang Mohamad Porkan Bin Haji Abang Budiman

## Note

**NOC Approval Settings** (http://localhost:8000/pages/general/approver) are used to:
- Determine WHO CAN approve (authorization check)
- Show approval buttons to authorized users only

**Actual Approver Data** comes from:
- NOC table fields: `first_approver_id` and `second_approver_id`
- Set when user clicks "Approve" button
- Loaded via Eloquent relationships: `firstApprover()` and `secondApprover()`
