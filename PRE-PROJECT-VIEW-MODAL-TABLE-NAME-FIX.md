# Pre-Project View Modal Table Name Fix

## Issue
When approvers clicked the "View" button on Pre-Project list page, they received "Failed to load pre-project data" error.

## Root Cause
The system was still referencing the old pivot table name `noc_pre_project` instead of the current table name `noc_project`. The table was renamed in a previous migration, but the code wasn't fully updated.

## Error Details
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'monitoring.noc_pre_project' doesn't exist
```

## Files Fixed

### 1. app/Http/Controllers/Pages/PageController.php
**Line ~1806**: Updated pivot table reference in `preProjectEdit` method
```php
// OLD (incorrect)
$pivotData = \DB::table('noc_pre_project')
    ->where('noc_id', $noc->id)
    ->where('pre_project_id', $preProject->id)
    ->first();

// NEW (correct)
$pivotData = \DB::table('noc_project')
    ->where('noc_id', $noc->id)
    ->where('pre_project_id', $preProject->id)
    ->first();
```

### 2. app/Models/PreProject.php
**Line ~172**: Updated relationship definition to use correct table name
```php
// OLD (incorrect)
public function nocs()
{
    return $this->belongsToMany(Noc::class, 'noc_pre_project');
}

// NEW (correct)
public function nocs()
{
    return $this->belongsToMany(Noc::class, 'noc_project', 'pre_project_id', 'noc_id');
}
```

## Cache Clearing
Ran `php artisan optimize:clear` to clear all caches:
- Configuration cache
- Application cache
- Compiled views
- Events cache
- Routes cache

## Testing
The View Pre-Project Details modal should now work correctly for approvers, showing:
- Basic project information
- Cost breakdown
- Project location details
- Site information
- Implementation details
- Bill of Quantity attachment (if exists)
- Approval History (if submitted)
- Project Changes from NOC (if exists)
- NOC Attachments (if exists)

## Status
✅ **FIXED** - Pre-Project view modal now loads successfully with all NOC-related data.
