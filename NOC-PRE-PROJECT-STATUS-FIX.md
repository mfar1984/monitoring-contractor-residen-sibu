# NOC Pre-Project Status Update Fix

## Problem
When creating a NOC (Notice of Change) and importing projects, the related pre-project status was not being updated from "Approved" to "NOC". This caused:
- Pre-projects to still be included in budget calculations
- Pre-projects to remain editable/deletable
- No visual indicator that the project is already in a NOC

## Solution
Updated 3 methods in `PageController.php` to automatically update pre-project status:

### 1. `projectNocStore()` - Create NOC
When a NOC is created and projects are imported:
- Update project status to NOC note name
- **Update related pre-project status to "NOC"**

```php
if ($project->pre_project_id) {
    $preProject = \App\Models\PreProject::find($project->pre_project_id);
    if ($preProject) {
        $preProject->update(['status' => 'NOC']);
    }
}
```

### 2. `projectNocReject()` - Reject NOC
When a NOC is rejected:
- Rollback project status to "Active"
- **Rollback pre-project status to "Approved"**

```php
if ($project->pre_project_id) {
    $preProject = \App\Models\PreProject::find($project->pre_project_id);
    if ($preProject) {
        $preProject->update(['status' => 'Approved']);
    }
}
```

### 3. `projectNocDelete()` - Delete NOC
When a NOC is deleted:
- Rollback project status to "Active"
- **Rollback pre-project status to "Approved"**

```php
if ($project->pre_project_id) {
    $preProject = \App\Models\PreProject::find($project->pre_project_id);
    if ($preProject) {
        $preProject->update(['status' => 'Approved']);
    }
}
```

## Status Flow

### Normal Flow:
1. Pre-project created → Status: "Draft"
2. Pre-project submitted → Status: "Waiting for EPU Approval"
3. Pre-project approved → Status: "Approved"
4. Project transferred → Status: "Approved" (still in pre-project list)
5. **NOC created with project** → Status: **"NOC"** ✅
6. Pre-project excluded from budget calculation ✅

### Rollback Flow:
1. NOC rejected/deleted → Pre-project status: **"Approved"** ✅
2. Pre-project included back in budget calculation ✅

## Database Relationships

```
pre_projects (id, name, status)
    ↓ (one-to-one)
projects (id, name, pre_project_id, status)
    ↓ (many-to-many via noc_project)
nocs (id, noc_number, status)
```

## Testing

### Test Case 1: Create NOC
1. Go to `/pages/project/noc/create`
2. Import project "Projek Baik Pulih Ruai Rumah Panjang"
3. Submit NOC
4. Check `/pages/pre-project` → Status should be "NOC" ✅

### Test Case 2: Delete NOC
1. Go to `/pages/project/noc`
2. Delete a draft NOC
3. Check `/pages/pre-project` → Status should rollback to "Approved" ✅

### Test Case 3: Reject NOC
1. Go to `/pages/project/noc/{id}`
2. Reject the NOC
3. Check `/pages/pre-project` → Status should rollback to "Approved" ✅

## Verification Query

```php
// Check pre-project status
$preProject = \App\Models\PreProject::find(1);
echo $preProject->status; // Should be "NOC" if in NOC

// Check if project is in NOC
$project = \App\Models\Project::where('pre_project_id', 1)->first();
$inNoc = \DB::table('noc_project')->where('project_id', $project->id)->exists();
echo $inNoc ? 'In NOC' : 'Not in NOC';
```

## Files Modified
- `app/Http/Controllers/Pages/PageController.php`
  - Line ~1984: `projectNocStore()` method
  - Line ~2120: `projectNocReject()` method
  - Line ~2162: `projectNocDelete()` method

## Impact
✅ Pre-projects with status "NOC" are excluded from budget calculation
✅ Pre-projects with status "NOC" cannot be edited/deleted
✅ Clear visual indicator in pre-project list
✅ Automatic status rollback when NOC is rejected/deleted
✅ No manual intervention required

## Date Fixed
February 19, 2026
