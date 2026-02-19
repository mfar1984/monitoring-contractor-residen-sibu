# Pre-Project Parliament/DUN Column Fix

## Issue Description

The Parliament/DUN column in the Pre-Project table at `http://localhost:8000/pages/pre-project` was displaying "-" (empty) instead of showing the actual Parliament or DUN name.

## Root Cause

The blade view was only checking for `$preProject->parliament` relationship but not checking for `$preProject->dunBasic` relationship. 

In the Pre-Project system, there are TWO different sets of Parliament/DUN fields:

1. **Basic Information Section**:
   - `parliament_id` → `parliament` relationship
   - `dun_basic_id` → `dunBasic` relationship
   - This is what should be displayed in the table column

2. **Project Location Section**:
   - `parliament_location_id` → `parliamentLocation` relationship
   - `dun_id` → `dun` relationship
   - This is used for detailed location information

## Changes Made

### 1. Updated Blade View (`resources/views/pages/pre-project.blade.php`)

**Before:**
```blade
<td>{{ $preProject->parliament ? $preProject->parliament->name : '-' }}</td>
```

**After:**
```blade
<td>{{ $preProject->parliament ? $preProject->parliament->name : ($preProject->dunBasic ? $preProject->dunBasic->name : '-') }}</td>
```

**Logic:**
- First check if `parliament` exists → display Parliament name
- If not, check if `dunBasic` exists → display DUN name
- If neither exists → display "-"

### 2. Updated Controller (`app/Http/Controllers/Pages/PageController.php`)

Added `dunBasic` to the eager-loaded relationships:

**Before:**
```php
$preProjects = \App\Models\PreProject::with([
    'residenCategory', 
    'agencyCategory', 
    'parliament', 
    'projectCategory',
    // ... other relationships
])->orderBy('created_at', 'desc')->get();
```

**After:**
```php
$preProjects = \App\Models\PreProject::with([
    'residenCategory', 
    'agencyCategory', 
    'parliament',
    'dunBasic',  // ← Added this
    'projectCategory',
    // ... other relationships
])->orderBy('created_at', 'desc')->get();
```

**Why:** Eager loading prevents N+1 query problems and ensures the relationship data is available.

## Database Structure Reference

The `pre_projects` table has these Parliament/DUN fields:

```sql
-- Basic Information (displayed in table column)
parliament_id          → parliaments.id
dun_basic_id          → duns.id

-- Project Location (used in print view)
parliament_location_id → parliaments.id
dun_id                → duns.id
```

## Model Relationships

The `PreProject` model has these relationships defined:

```php
// Basic Information
public function parliament()
{
    return $this->belongsTo(Parliament::class);
}

public function dunBasic()
{
    return $this->belongsTo(Dun::class, 'dun_basic_id');
}

// Project Location
public function parliamentLocation()
{
    return $this->belongsTo(Parliament::class, 'parliament_location_id');
}

public function dun()
{
    return $this->belongsTo(Dun::class);
}
```

## Testing

To verify the fix:

1. Navigate to `http://localhost:8000/pages/pre-project`
2. Check the Parliament/DUN column in the table
3. For pre-projects with Parliament selected → should show Parliament name
4. For pre-projects with DUN selected → should show DUN name
5. For pre-projects with neither → should show "-"

## Notes

- The print view (`pre-project-print.blade.php`) correctly displays Parliament and DUN from the Project Location section, which is appropriate for detailed location information
- The table column displays Parliament/DUN from the Basic Information section, which is the primary constituency assignment
- Both implementations are correct for their respective purposes

## Files Modified

1. `resources/views/pages/pre-project.blade.php` - Updated table column display logic
2. `app/Http/Controllers/Pages/PageController.php` - Added `dunBasic` to eager loading

## Status

✅ **FIXED** - Parliament/DUN column now displays correctly in the Pre-Project table
