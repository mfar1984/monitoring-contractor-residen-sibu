# NOC Create Button Budget Validation Fix

## Masalah
Butang "Create NOC" di halaman Create NOC (`/pages/project/noc/create`) tidak mengikut keperluan budget yang betul:

**Sebelum:**
- ❌ Butang ENABLED bila remaining budget > 0 (masih ada baki)
- ❌ Butang DISABLED hanya bila over budget (remaining < 0)
- ❌ Butang ENABLED walaupun ada row kosong (tiada New Cost)

**Keperluan Sebenar:**
- ✅ Butang DISABLED bila remaining budget > 0 (masih ada baki)
- ✅ Butang DISABLED bila over budget (remaining < 0)
- ✅ Butang DISABLED bila ada row kosong (row tanpa New Cost)
- ✅ Butang ENABLED hanya bila:
  - Remaining budget = RM 0.00 (budget habis sepenuhnya)
  - TIADA row kosong (semua row ada New Cost)

## Rasional
NOC (Notice of Change) hanya boleh dibuat apabila:
1. SEMUA budget telah diagihkan sepenuhnya kepada projek-projek
2. TIADA row kosong dalam table (semua row mesti ada New Cost atau di-delete)

Ini memastikan:
- Tiada budget yang tertinggal/tidak digunakan
- Semua perubahan projek telah diambil kira
- Budget tracking yang tepat dan lengkap
- Data yang bersih tanpa row kosong

## Penyelesaian

### 1. Update JavaScript Logic
**File**: `resources/views/pages/project-noc-create.blade.php`

**Tambah Function untuk Check Empty Rows:**
```javascript
function checkForEmptyRows() {
    const rows = document.querySelectorAll('#projectsTable tbody tr');
    let hasEmpty = false;
    
    rows.forEach(row => {
        const kosBaru = row.querySelector('.kos-baru-input');
        const kosBaruValue = parseFloat(kosBaru?.value) || 0;
        
        // If row exists but has no New Cost, it's considered empty
        if (kosBaru && kosBaruValue === 0) {
            hasEmpty = true;
        }
    });
    
    return hasEmpty;
}
```

**Update Budget Summary Logic:**
```javascript
// Check for empty rows
const hasEmptyRows = checkForEmptyRows();

if (remaining < 0) {
    // Over budget - show error, disable button
    warningDiv.style.display = 'block';
    submitBtn.disabled = true;
} else if (hasEmptyRows) {
    // Has empty rows - show warning, disable button
    emptyRowWarning.style.display = 'block';
    submitBtn.disabled = true;
} else if (remaining > 0) {
    // Still have remaining budget - show info, disable button
    infoDiv.style.display = 'block';
    submitBtn.disabled = true;
} else {
    // Remaining = RM 0.00 AND no empty rows - enable button
    submitBtn.disabled = false;
}
```

### 2. Tambah Empty Row Warning Message
**File**: `resources/views/pages/project-noc-create.blade.php`

```html
<!-- Empty Row Warning Message -->
<div id="emptyRowWarning" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #dc3545; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
    <div style="display: flex; align-items: flex-start; gap: 10px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #dc3545; flex-shrink: 0; margin-top: 1px;">warning</span>
        <div style="line-height: 1.5;">
            <strong style="display: block; margin-bottom: 4px; color: #333;">Empty Rows Detected</strong>
            <span style="color: #666;">Please delete empty rows (rows without New Cost) before creating NOC.</span>
        </div>
    </div>
</div>
```

## Contoh Scenario

### Scenario 1: Over Budget (Remaining < 0)
```
Total NOC Budget: RM 1,500,000.00
Total Allocated:  RM 5,100,000.00
Remaining:        RM -3,600,000.00 (MERAH)

Status: ❌ Butang DISABLED
Mesej:  "Budget Exceeded" (merah)
```

### Scenario 2: Ada Row Kosong
```
Total NOC Budget: RM 1,500,000.00
Total Allocated:  RM 1,500,000.00
Remaining:        RM 0.00 (HITAM)
Rows:             3 rows (1 imported, 1 with New Cost, 1 EMPTY)

Status: ❌ Butang DISABLED
Mesej:  "Empty Rows Detected" (merah)
Action: Delete row kosong dulu
```

### Scenario 3: Masih Ada Baki (Remaining > 0)
```
Total NOC Budget: RM 1,500,000.00
Total Allocated:  RM 10,000.00
Remaining:        RM 1,490,000.00 (HITAM)

Status: ❌ Butang DISABLED
Mesej:  "Budget Not Fully Allocated" (kuning)
```

### Scenario 4: Budget Perfect + Tiada Row Kosong ✅
```
Total NOC Budget: RM 1,500,000.00
Total Allocated:  RM 1,500,000.00
Remaining:        RM 0.00 (HITAM)
Rows:             2 rows (semua ada New Cost)

Status: ✅ Butang ENABLED
Mesej:  Tiada mesej
```

## Visual Indicators

### Remaining Budget Color
- **Merah**: Remaining < 0 (over budget)
- **Hitam**: Remaining >= 0 (normal)

### Warning/Info Messages (Priority Order)
1. **Merah** (error icon): Budget Exceeded - bila over budget
2. **Merah** (warning icon): Empty Rows Detected - bila ada row kosong
3. **Kuning** (info icon): Budget Not Fully Allocated - bila masih ada baki
4. **Tiada**: Budget perfect + tiada row kosong

### Button State
- **Disabled** (opacity 0.5, cursor not-allowed):
  - Remaining < 0 (over budget)
  - Ada row kosong (row tanpa New Cost)
  - Remaining > 0 (masih ada baki)
- **Enabled** (opacity 1, cursor pointer):
  - Remaining = RM 0.00 (budget habis sepenuhnya)
  - TIADA row kosong (semua row ada New Cost)

## Workflow Pengguna

1. **Import Projects** atau **Add New Projects**
   - Total NOC Budget dikira dari projek yang diimport
   - Total Allocated dikira dari Kos Baru yang dimasukkan

2. **Masukkan Kos Baru untuk setiap projek**
   - Sistem kira Remaining Budget secara real-time
   - Mesej warning/info muncul jika perlu

3. **Delete row kosong (jika ada)**
   - Row yang tiada New Cost mesti di-delete
   - Klik butang delete (merah) di sebelah kanan row

4. **Pastikan Remaining Budget = RM 0.00**
   - Adjust Kos Baru sehingga remaining = 0
   - Atau import lebih banyak projek untuk tambah Total NOC Budget

5. **Butang "Create NOC" akan enabled**
   - Hanya bila Remaining Budget = RM 0.00
   - DAN tiada row kosong
   - Upload attachments dan submit

## Validation Rules

### Row dianggap KOSONG bila:
- Row ada dalam table
- Tetapi New Cost (Kos Baru) = 0 atau kosong
- Row ini mesti di-delete sebelum boleh create NOC

### Row dianggap VALID bila:
- Row imported dari project (ada Project No, Current Project Name, Current Cost)
- Row baru dengan New Cost > 0

## Status
✅ **SELESAI** - Butang Create NOC kini:
- Hanya enabled bila remaining budget = RM 0.00
- DAN tiada row kosong dalam table

## Nota Penting
- Validation ini adalah CLIENT-SIDE (JavaScript)
- Perlu tambah SERVER-SIDE validation di controller juga untuk keselamatan
- Pengguna mesti:
  1. Delete semua row kosong
  2. Allocate semua budget (remaining = RM 0.00)
  3. Baru boleh create NOC
