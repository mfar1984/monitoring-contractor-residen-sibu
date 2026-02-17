# Project Transfer Manual Implementation Summary

## Overview
Sistem transfer project telah diubah dari auto-transfer kepada manual transfer mengikut keperluan user.

## Changes Made

### 1. Removed Auto-Transfer Logic
**File**: `app/Http/Controllers/Pages/PageController.php`
- Buang auto-transfer logic dari method `preProjectUpdate()`
- Pre-Project tidak lagi auto-transfer bila status bertukar ke "Approved"

### 2. Updated ProjectTransferService
**File**: `app/Services/ProjectTransferService.php`
- Method `transfer()` sekarang terima 3 parameters:
  - `PreProject $preProject`
  - `string $projectNumber` (user input)
  - `string $projectYear` (user input)
- Buang method `generateUniqueProjectNumber()` (tidak diperlukan lagi)
- Update method `canTransfer()` untuk buang validation status "Approved"
- Transfer service sekarang akan set Pre-Project status ke "Approved" selepas transfer

### 3. Database Changes

#### Migration 1: Add project_year column
**File**: `database/migrations/2026_02_15_202625_add_project_year_to_projects_table.php`
- Tambah column `project_year` (string, nullable) di table `projects`

#### Migration 2: Remove unique constraint
**File**: `database/migrations/2026_02_15_202912_remove_unique_constraint_from_project_number_in_projects_table.php`
- Buang unique constraint dari column `project_number`
- User boleh masukkan No Projek sendiri tanpa validation unique

### 4. Updated Project Model
**File**: `app/Models/Project.php`
- Tambah `project_year` dalam `$fillable` array

### 5. Updated PreProject Model
**File**: `app/Models/PreProject.php`
- Tambah relationship `project()` untuk check jika pre-project sudah ditransfer

### 6. New Routes
**File**: `routes/web.php`
```php
Route::get('/pages/project/transfer', [PageController::class, 'projectTransferCreate'])
    ->name('pages.project.transfer.create');
Route::post('/pages/project/transfer', [PageController::class, 'projectTransferStore'])
    ->name('pages.project.transfer.store');
```

### 7. New Controller Methods
**File**: `app/Http/Controllers/Pages/PageController.php`

#### projectTransferCreate()
- Display form untuk transfer project
- Load pre-projects yang belum ditransfer (status != 'Approved')
- Filter by user's Parliament/DUN

#### projectTransferStore()
- Validate input (pre_project_id, project_number, project_year)
- Check if pre-project sudah ditransfer
- Call ProjectTransferService untuk transfer
- Set Pre-Project status ke "Approved" selepas transfer
- Redirect ke project list dengan success message

### 8. New View: Transfer Project Form
**File**: `resources/views/pages/project-transfer.blade.php`

**Form Fields:**
- Dropdown Pre-Project (filtered by user Parliament/DUN)
- Input No Projek (required, max 255 characters)
- Input Tahun (required, 4 digits, default: current year)

**Features:**
- Show message jika tiada pre-project available
- Disable submit button jika tiada pre-project
- Validation error display
- Success/error message display

### 9. Updated Project List Page
**File**: `resources/views/pages/project.blade.php`
- Tambah butang "Transfer Project" di header
- Tambah column "Year" di table
- Update colspan untuk empty state

## Workflow

### Old Workflow (Auto-Transfer)
1. Pre-Project dicipta
2. Edit Pre-Project → tukar status ke "Approved"
3. System auto-transfer ke Project dengan auto-generated project number
4. Pre-Project status jadi "Approved"

### New Workflow (Manual Transfer)
1. Pre-Project dicipta oleh Parliament/DUN users
2. Pre-Project diluluskan oleh approvers (future: multiple 1st layer approvers)
3. User pergi ke `/pages/project` → klik "Transfer Project"
4. Pilih Pre-Project dari dropdown
5. Masukkan No Projek yang diluluskan
6. Masukkan Tahun
7. Klik "Transfer"
8. System transfer Pre-Project ke Project
9. Pre-Project status auto-update ke "Approved"

## Database Schema Changes

### projects table
```sql
ALTER TABLE projects 
ADD COLUMN project_year VARCHAR(255) NULL AFTER project_number;

ALTER TABLE projects 
DROP INDEX projects_project_number_unique;
```

## Testing

### Test Transfer Functionality
1. Login sebagai Parliament/DUN user
2. Pergi ke `/pages/project`
3. Klik butang "Transfer Project"
4. Pilih Pre-Project dari dropdown
5. Masukkan No Projek (contoh: PROJ/2026/001)
6. Masukkan Tahun (contoh: 2026)
7. Klik "Transfer"
8. Verify:
   - Project baru dicipta dengan No Projek dan Tahun yang dimasukkan
   - Pre-Project status bertukar ke "Approved"
   - Redirect ke project list dengan success message

### Test Validation
1. Cuba transfer tanpa pilih Pre-Project → Error
2. Cuba transfer tanpa No Projek → Error
3. Cuba transfer tanpa Tahun → Error
4. Cuba transfer Pre-Project yang sudah ditransfer → Error message

## Future Enhancements

### Pre-Project Approval System
**Location**: `/pages/general/application`
- Tambah setting untuk Pre-Project Approval (1st layer sahaja)
- Multiple approvers yang boleh di-tag
- Approval workflow sebelum boleh transfer

### Budget Tracking
- Parliament/DUN users ada budget sendiri
- Track budget usage untuk Pre-Project creation
- Validation budget sebelum create Pre-Project

## Files Modified

1. `app/Http/Controllers/Pages/PageController.php`
2. `app/Services/ProjectTransferService.php`
3. `app/Models/Project.php`
4. `app/Models/PreProject.php`
5. `routes/web.php`
6. `resources/views/pages/project.blade.php`

## Files Created

1. `database/migrations/2026_02_15_202625_add_project_year_to_projects_table.php`
2. `database/migrations/2026_02_15_202912_remove_unique_constraint_from_project_number_in_projects_table.php`
3. `resources/views/pages/project-transfer.blade.php`
4. `PROJECT-TRANSFER-MANUAL-IMPLEMENTATION-SUMMARY.md`

## Notes

- Auto-transfer logic telah dibuang sepenuhnya
- User sekarang ada full control untuk masukkan No Projek dan Tahun
- Pre-Project status "Approved" bermaksud sudah ditransfer ke Project
- System tidak validate unique project_number (user responsible untuk masukkan No Projek yang betul)
