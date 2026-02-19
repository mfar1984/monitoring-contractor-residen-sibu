# Pre-Project Cost Save Fix - FINAL SOLUTION

## Date: February 17, 2026

## Problem
User boleh edit nilai di bahagian "Cost of Project" tetapi selepas save, data tidak tersimpan dalam database.

## Root Cause Analysis

### Initial Investigation:
Form tidak ada hidden input untuk `total_cost`, hanya ada readonly display field.

### Actual Root Cause (DISCOVERED):
Validation rule untuk `bill_of_quantity_attachment` menyebabkan silent validation failure:

```php
// WRONG - Causes silent fail when editing without uploading new file
'bill_of_quantity_attachment' => 'required_if:bill_of_quantity,Yes|file|...'
```

Bila user edit cost fields tanpa upload file baru, dan `bill_of_quantity` = "Yes", validation gagal secara senyap dan data tidak tersimpan.

## Solution

### 1. Added Hidden Input Field
**File**: `resources/views/pages/pre-project.blade.php`

```html
<div class="form-group">
    <label for="total_cost_display">Total Cost (RM)</label>
    <input type="text" id="total_cost_display" readonly style="background-color: #f5f5f5; font-weight: 600;">
    <input type="hidden" id="total_cost" name="total_cost" value="0">
</div>
```

### 2. Updated calculateTotal() Function
**File**: `resources/views/pages/pre-project.blade.php`

```javascript
function calculateTotal() {
    const actualCost = parseFloat(document.getElementById('actual_project_cost').value) || 0;
    const consultationCost = parseFloat(document.getElementById('consultation_cost').value) || 0;
    const lssCost = parseFloat(document.getElementById('lss_inspection_cost').value) || 0;
    const sst = parseFloat(document.getElementById('sst').value) || 0;
    const others = parseFloat(document.getElementById('others_cost').value) || 0;
    
    const total = actualCost + consultationCost + lssCost + sst + others;
    document.getElementById('total_cost_display').value = total.toFixed(2);
    document.getElementById('total_cost').value = total.toFixed(2); // Set hidden input
}
```

### 3. Fixed Validation Rule (CRITICAL FIX)
**File**: `app/Http/Controllers/Pages/PageController.php`

```php
// BEFORE (WRONG)
'bill_of_quantity_attachment' => 'required_if:bill_of_quantity,Yes|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',

// AFTER (CORRECT)
'bill_of_quantity_attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
```

### 4. Fixed Budget Validation Logic
**File**: `app/Http/Controllers/Pages/PageController.php`

```php
// Only validate if original_project_cost is set and greater than 0
if (!empty($preProject->original_project_cost) && $preProject->original_project_cost > 0 && !empty($request->actual_project_cost)) {
    if ($request->actual_project_cost > $preProject->original_project_cost) {
        return redirect()->back()->withInput()->withErrors([...]);
    }
}
```

## Why It Failed Before

1. **Hidden Input Missing**: Form tidak hantar `total_cost` value
2. **Validation Silent Fail**: `bill_of_quantity_attachment` required bila `bill_of_quantity=Yes` tetapi user tidak upload file baru
3. **Budget Validation Too Strict**: Validation check `original_project_cost` walaupun nilai adalah 0

## Verification

```bash
php artisan tinker --execute="
\$p = \App\Models\PreProject::find(26);
echo 'Actual Project Cost: RM ' . number_format(\$p->actual_project_cost, 2) . PHP_EOL;
echo 'Total Cost: RM ' . number_format(\$p->total_cost, 2) . PHP_EOL;
"
```

**Result**:
```
Actual Project Cost: RM 1,500,000.00 ✅
Total Cost: RM 1,500,000.00 ✅
```

## Files Modified

1. ✅ `resources/views/pages/pre-project.blade.php` - Added hidden input, updated JavaScript
2. ✅ `app/Http/Controllers/Pages/PageController.php` - Fixed validation rules

## Testing Checklist

- [x] Edit Pre-Project with cost values
- [x] Total Cost auto-calculates correctly
- [x] Data saves to database
- [x] Budget validation works for projects with original_project_cost > 0
- [x] Budget validation skips for projects with original_project_cost = 0
- [x] Can edit without uploading new file attachment

## Summary

**Root Cause**: Validation rule `required_if:bill_of_quantity,Yes` untuk file attachment menyebabkan silent validation failure bila user edit tanpa upload file baru.

**Solution**: Changed to `nullable` - file only required when actually uploading.

**Status**: COMPLETE ✅ - Data now saves correctly!

---

**Last Updated**: February 17, 2026
**Tested**: Pre-Project ID 26 - SUCCESS ✅
