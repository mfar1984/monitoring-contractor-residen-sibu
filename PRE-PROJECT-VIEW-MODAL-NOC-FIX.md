# Pre-Project View Modal NOC Data Fix

## Issue Summary

The Pre-Project view modal was failing to load NOC data with error:
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'monitoring.noc_pre_project' doesn't exist
```

## Root Cause

The NOC system was restructured to work with Projects instead of Pre-Projects directly:
- Old table: `noc_pre_project` with `pre_project_id` column
- New table: `noc_project` with `project_id` column
- Relationship flow: `PreProject (1:1) → Project (M:N) → NOC`

The view modal was still trying to load NOCs directly from Pre-Projects using the old table structure.

## Solution Implemented

### 1. Updated PreProject Model (`app/Models/PreProject.php`)

Added `nocs()` relationship method that accesses NOCs through the Project relationship:

```php
/**
 * Get all NOCs through the project (if this pre-project has been transferred)
 * Note: NOCs are now associated with Projects, not Pre-Projects
 */
public function nocs()
{
    // If this pre-project has been transferred to a project, get NOCs through the project
    if ($this->project) {
        return $this->project->nocs();
    }
    
    // Return empty relationship if no project exists
    return $this->belongsToMany(Noc::class, 'noc_project', 'project_id', 'noc_id')->whereRaw('1 = 0');
}

/**
 * Get the project if this pre-project has been transferred
 */
public function project()
{
    return $this->hasOne(Project::class);
}
```

### 2. Updated PageController (`app/Http/Controllers/Pages/PageController.php`)

Modified `preProjectEdit()` method (line ~1777) to:
- Load `project` relationship instead of trying to load `nocs` directly
- Check if Pre-Project has been transferred to a Project
- Load NOC data through Project relationship if exists
- Query `noc_project` table using `project_id` instead of `pre_project_id`
- Build both `noc_changes` array and `nocs` collection for response

```php
public function preProjectEdit($id)
{
    $preProject = \App\Models\PreProject::with([
        // ... other relationships
        'project' // Load project relationship to check if transferred
    ])->findOrFail($id);
    
    // Get NOC changes if this pre-project has been transferred to a project
    $nocChanges = [];
    $nocs = [];
    
    if ($preProject->project) {
        // Load NOCs through the project relationship
        $project = \App\Models\Project::with([
            'nocs.creator.parliament',
            'nocs.creator.dun',
            'nocs.firstApprover',
            'nocs.secondApprover'
        ])->find($preProject->project->id);
        
        if ($project && $project->nocs) {
            foreach ($project->nocs as $noc) {
                $pivotData = \DB::table('noc_project')
                    ->where('noc_id', $noc->id)
                    ->where('project_id', $project->id)
                    ->first();
                
                if ($pivotData) {
                    $nocNote = \App\Models\NocNote::find($pivotData->noc_note_id);
                    $nocChanges[] = [
                        'noc_number' => $noc->noc_number,
                        'tahun_rtp' => $pivotData->tahun_rtp,
                        'no_projek' => $pivotData->no_projek,
                        'nama_projek_asal' => $pivotData->nama_projek_asal,
                        'nama_projek_baru' => $pivotData->nama_projek_baru,
                        'kos_asal' => $pivotData->kos_asal,
                        'kos_baru' => $pivotData->kos_baru,
                        'agensi_pelaksana_asal' => $pivotData->agensi_pelaksana_asal,
                        'agensi_pelaksana_baru' => $pivotData->agensi_pelaksana_baru,
                        'noc_note_name' => $nocNote ? $nocNote->name : null,
                    ];
                }
            }
            
            // Add NOC data to response
            $nocs = $project->nocs;
        }
    }
    
    $preProject->noc_changes = $nocChanges;
    $preProject->nocs = $nocs;
    
    return response()->json($preProject);
}
```

### 3. View File Already Implemented (`resources/views/pages/pre-project.blade.php`)

The JavaScript `viewPreProject()` function (line ~1305) already has complete code to display:

#### Approval History Section
- Submitted to EPU (cyan border)
- First Approval (green border)
- Second Approval (blue border)
- Rejection (red border)
- Shows approver name, date, and remarks

#### Project Changes Section (from NOC)
Table with columns:
- NOC Number
- Tahun RTP
- No Projek
- Nama Projek Asal
- Nama Projek Baru (blue highlight if changed)
- Kos Asal (RM) with thousand separator
- Kos Baru (RM) (blue highlight if changed)
- Agensi Pelaksana Asal
- Agensi Pelaksana Baru (blue highlight if changed)
- Catatan (NOC Note)

#### NOC Attachments Section
- NOC Letter download link with Material Icon
- NOC Project List download link with Material Icon
- Shows "No attachment" if files don't exist

### 4. Cache Cleared

Ran `php artisan optimize:clear` to clear all caches.

## Testing Results

### Database Verification
```bash
Pre-Project ID: 4
Pre-Project Name: Bawang Assan Water Supply Expansion
Has Project: Yes (ID: 1)
Project Number: RTP/2026/1467/SBU/L/44a
NOCs Count: 1
  - NOC: NOC/2026/004
```

### Controller Method Test
```bash
Found 1 NOCs
Processing NOC: NOC/2026/004
Found pivot data
Tahun RTP: 2026
No Projek: RTP/2026/1467/SBU/L/44a
Nama Projek Asal: Bawang Assan Water Supply Expansion
```

## Expected Behavior

### For Pre-Projects Transferred to Projects with NOCs
Example: Pre-Project ID 4 (Bawang Assan) → Project ID 1 → NOC/2026/004

View modal will display:
1. ✅ Basic Information section
2. ✅ Cost of Project section
3. ✅ Project Location section
4. ✅ Site Information section
5. ✅ Implementation Details section
6. ✅ Approval History section (if submitted/approved)
7. ✅ Project Changes section (showing NOC changes with blue highlights)
8. ✅ NOC Attachments section (with download links)

### For Pre-Projects Not Transferred
Example: Pre-Project ID 26 (Bina baru pagar masjid)

View modal will display:
1. ✅ Basic Information section
2. ✅ Cost of Project section
3. ✅ Project Location section
4. ✅ Site Information section
5. ✅ Implementation Details section
6. ✅ Approval History section (if submitted/approved)
7. ❌ Project Changes section (hidden - no NOC data)
8. ❌ NOC Attachments section (hidden - no NOC data)

## Files Modified

1. `app/Models/PreProject.php` - Added `nocs()` and `project()` relationships
2. `app/Http/Controllers/Pages/PageController.php` - Updated `preProjectEdit()` method

## Files Already Correct

1. `resources/views/pages/pre-project.blade.php` - JavaScript already has display code

## Status

✅ **COMPLETE** - Implementation finished and tested successfully.

The view modal now correctly loads NOC data for Pre-Projects that have been transferred to Projects, and gracefully handles Pre-Projects that haven't been transferred yet.

## Next Steps for User

1. Log in to the system as an approver user
2. Navigate to Pre-Project list page
3. Click "View" button on Pre-Project ID 4 (Bawang Assan Water Supply Expansion)
4. Verify the modal displays:
   - All basic project information
   - Approval history (if any)
   - Project Changes table with NOC data
   - NOC Attachments with download links
5. Verify blue highlighting works for changed values
6. Test with Pre-Project ID 26 to verify sections hide when no NOC data exists
