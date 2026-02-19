# NOC Empty Row Validation Fix

## Masalah

Sistem menunjukkan "Empty Rows Detected" walaupun semua row sudah diisi dengan betul.

**Scenario yang SALAH didetect sebagai empty:**
```
Row 1: Imported project
- Current Cost: RM 1,500,000
- New Cost: (kosong - "Leave empty if no change")
- Status: Sepatutnya OK (tiada perubahan cost)
- Tetapi sistem detect sebagai EMPTY ❌

Row 2: New project  
- Current Cost: RM 0
- New Cost: RM 1,500,000
- Status: OK ✅
```

## Keperluan yang Betul

**Empty row** hanya untuk:
- ✅ **NEW projects** (ditambah dengan "Add New" button) yang tidak ada New Cost

**BUKAN empty row:**
- ✅ **Imported projects** tanpa New Cost (maksudnya tiada perubahan cost - ini OK!)
- ✅ **Imported projects** dengan New Cost (ada perubahan cost)
- ✅ **New projects** dengan New Cost

## Penyelesaian

Update `checkForEmptyRows()` function untuk check sama ada row adalah NEW project atau IMPORTED project:

```javascript
function checkForEmptyRows() {
    const tbody = document.getElementById('projectsTableBody');
    
    // Skip if table is empty (showing empty state message)
    const emptyStateRow = tbody.querySelector('tr td[colspan="10"]');
    if (emptyStateRow) {
        return false; // No actual rows, so no empty rows
    }
    
    const rows = tbody.querySelectorAll('tr');
    let hasEmpty = false;
    
    rows.forEach(row => {
        const kosBaru = row.querySelector('.kos-baru-input');
        const kosAsal = row.querySelector('.kos-asal-input');
        
        // Only check rows that have kos-baru-input field
        if (kosBaru && kosAsal) {
            const kosBaruValue = parseFloat(kosBaru.value) || 0;
            const kosAsalValue = parseFloat(kosAsal.value) || 0;
            
            // Check if this is a new project row (kosAsal input is disabled)
            const isNewProject = kosAsal.disabled || kosAsalValue === 0;
            
            if (isNewProject && kosBaruValue === 0) {
                // This is a new project row with no cost - EMPTY
                hasEmpty = true;
            }
            // Imported projects (kosAsal > 0) with empty New Cost are OK
            // They mean "no change to cost"
        }
    });
    
    return hasEmpty;
}
```

## Cara Bezakan NEW vs IMPORTED Project

**NEW Project:**
- Current Cost input field adalah **disabled** (background grey)
- ATAU Current Cost value = 0
- New Cost adalah **required** (mesti diisi)

**IMPORTED Project:**
- Current Cost input field adalah **readonly** (ada value, tidak boleh edit)
- Current Cost value > 0
- New Cost adalah **optional** (boleh kosong = tiada perubahan)

## Test Cases

### Test Case 1: Imported Project Tanpa New Cost (OK)
```
Row: Imported project
- Current Cost: RM 1,500,000 (readonly)
- New Cost: (kosong)

Expected:
- hasEmptyRows: FALSE ✅
- Button: Check budget allocation
- Reason: Imported project tanpa New Cost = tiada perubahan cost (OK)
```

### Test Case 2: New Project Tanpa New Cost (EMPTY)
```
Row: New project
- Current Cost: RM 0 (disabled)
- New Cost: (kosong)

Expected:
- hasEmptyRows: TRUE ✅
- Button: DISABLED
- Message: "Empty Rows Detected"
- Reason: New project mesti ada New Cost
```

### Test Case 3: New Project Dengan New Cost (OK)
```
Row: New project
- Current Cost: RM 0 (disabled)
- New Cost: RM 1,500,000

Expected:
- hasEmptyRows: FALSE ✅
- Button: Check budget allocation
- Reason: New project ada New Cost (OK)
```

### Test Case 4: Imported Project Dengan New Cost (OK)
```
Row: Imported project
- Current Cost: RM 1,500,000 (readonly)
- New Cost: RM 2,000,000

Expected:
- hasEmptyRows: FALSE ✅
- Button: Check budget allocation
- Reason: Imported project dengan perubahan cost (OK)
```

## Workflow Pengguna

### Untuk IMPORTED Projects:
1. Import project dari list
2. **OPTIONAL**: Isi New Cost jika nak ubah cost
3. Jika tidak isi New Cost = tiada perubahan cost (OK)
4. Sistem tidak akan detect sebagai empty row

### Untuk NEW Projects:
1. Klik "Add New" button
2. **REQUIRED**: Mesti isi New Cost
3. Jika tidak isi New Cost = empty row (ERROR)
4. Sistem akan detect dan disable butang CREATE NOC

## Files Modified

- `resources/views/pages/project-noc-create.blade.php` - Updated `checkForEmptyRows()` function

## Status

✅ **SELESAI** - Empty row detection kini betul:
- Imported projects tanpa New Cost = OK (tiada perubahan)
- New projects tanpa New Cost = EMPTY (error)
- Check menggunakan `kosAsal.disabled` atau `kosAsalValue === 0`



---

## Update: Real-Time Validation (17 Feb 2026)

### Masalah Tambahan

Walaupun `checkForEmptyRows()` function sudah betul, masih ada masalah:
1. Validation tidak run bila user klik "Add New Project"
2. Validation hanya run bila user ubah New Cost field (onchange event)
3. Jika field kosong, onchange tidak trigger
4. Button CREATE NOC jadi enabled walaupun ada empty row

### Penyelesaian Real-Time Validation

#### 1. Trigger Validation Selepas Add New Row

```javascript
function addNewProjectRow() {
    addProjectRow(null, '', '', 0, '', '', '', true);
    // Run validation immediately after adding new row
    updateBudgetSummary();
}
```

#### 2. Event Delegation untuk Real-Time Validation

```javascript
// Event delegation for real-time validation on input fields
document.getElementById('projectsTableBody').addEventListener('input', function(e) {
    if (e.target.classList.contains('kos-baru-input')) {
        updateBudgetSummary();
    }
});

// Also trigger validation on blur (when user leaves the field)
document.getElementById('projectsTableBody').addEventListener('blur', function(e) {
    if (e.target.classList.contains('kos-baru-input')) {
        updateBudgetSummary();
    }
}, true);
```

### Kelebihan Real-Time Validation

1. **Immediate Feedback**: Validation run sebaik sahaja user klik "Add New Project"
2. **Live Updates**: Validation run semasa user type dalam New Cost field
3. **Better UX**: User dapat feedback segera tanpa perlu tunggu submit form
4. **Prevent Errors**: User tidak boleh submit form dengan empty rows

### Test Scenario Real-Time

#### Scenario 1: Add New Project (Empty)
```
1. User klik "Add New Project"
2. Row baru ditambah ke table
3. ✅ Validation run IMMEDIATELY
4. ✅ Yellow warning muncul: "Empty Rows Detected"
5. ✅ Button CREATE NOC disabled
```

#### Scenario 2: Fill New Cost (Real-Time)
```
1. User ada empty row (dari Scenario 1)
2. User mula type dalam New Cost field
3. ✅ Validation run SEMASA user type (input event)
4. User enter valid cost (e.g., RM 1,500,000)
5. ✅ Warning hilang jika budget = RM 0.00
6. ✅ Button CREATE NOC enabled
```

#### Scenario 3: Delete Empty Row
```
1. User ada empty row
2. User klik delete button
3. ✅ Validation run dalam deleteRow() function
4. ✅ Warning hilang jika tiada empty rows
5. ✅ Button enabled jika budget = RM 0.00
```

### Validation Triggers

Validation (`updateBudgetSummary()`) akan run bila:
1. ✅ User klik "Add New Project" button
2. ✅ User klik "Import Project" button
3. ✅ User type dalam New Cost field (input event)
4. ✅ User keluar dari New Cost field (blur event)
5. ✅ User delete row
6. ✅ User ubah New Cost value (onchange event - existing)

### Files Modified

- `resources/views/pages/project-noc-create.blade.php`
  - Updated `addNewProjectRow()` to call `updateBudgetSummary()`
  - Added event delegation for `input` event on New Cost fields
  - Added event delegation for `blur` event on New Cost fields

## Status Final

✅ **COMPLETE** - Empty row validation dengan real-time detection:
- ✅ Validation run immediately bila add new row
- ✅ Validation run real-time bila user type
- ✅ Button CREATE NOC properly disabled bila ada empty rows
- ✅ User dapat immediate feedback melalui warning messages
- ✅ Prevent user submit form dengan incomplete data


---

## Final Testing & Confirmation (17 Feb 2026)

### Test Result

✅ **CONFIRMED WORKING** - Empty row validation berfungsi dengan betul:

**Test Scenario:**
1. Import 2 projects (Total: RM 1,500,000)
2. Allocate RM 1,500,000 to first project (Remaining: RM 0.00)
3. Click "Add New" to add row 3 (empty row)
4. Result: Button CREATE NOC **DISABLED** ✅
5. Delete empty row
6. Result: Button CREATE NOC **ENABLED** ✅

### Validation Logic Confirmed

```javascript
function checkForEmptyRows() {
    // Check each row
    // If row is NEW project (kosAsal disabled or = 0)
    // AND New Cost is empty or invalid
    // Then mark as EMPTY
    
    // Imported projects with empty New Cost = OK (no change)
    // New projects with empty New Cost = EMPTY (error)
}
```

### Button State Logic

CREATE NOC button **DISABLED** when:
1. ❌ Remaining budget < 0 (over budget)
2. ❌ Remaining budget > 0 (not fully allocated)
3. ❌ Has empty rows (new projects without New Cost)

CREATE NOC button **ENABLED** when:
1. ✅ Remaining budget = RM 0.00 exactly
2. ✅ NO empty rows

### Status

✅ **COMPLETE & TESTED** - Validation berfungsi seperti yang dikehendaki.
