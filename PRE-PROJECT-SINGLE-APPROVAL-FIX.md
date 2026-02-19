# Pre-Project Single Approval Fix

## Masalah
Di halaman Pre-Project (`/pages/pre-project`), modal approval menunjukkan teks yang salah:
- ❌ "Status will change to 'Waiting for Approver 2'" 
- ❌ Memberi gambaran bahawa ada DUA kali approval

## Keperluan Sistem
- **Pre-Project**: Hanya SATU kali approval sahaja
  - Status flow: `Waiting for Approver 1` → `Waiting for EPU Approval`
  - Approver: Mana-mana user dalam senarai Pre-Project Approvers
  
- **Project**: DUA kali approval (berbeza dari Pre-Project)
  - Status flow: `Waiting for Approval 1` → `Waiting for Approval 2` → `Approved`
  - Approver 1: User yang ditetapkan dalam Application Settings
  - Approver 2: User yang ditetapkan dalam Application Settings

## Penyelesaian

### 1. Betulkan Teks dalam Modal Approval
**File**: `resources/views/pages/pre-project.blade.php`

**Sebelum**:
```html
<li id="approveStatusInfo">Status will change to next approval level</li>
```

**Selepas**:
```html
<li>Status will change to "Waiting for EPU Approval"</li>
```

### 2. Buang JavaScript yang Tidak Perlu
**File**: `resources/views/pages/pre-project.blade.php`

Buang kod JavaScript yang mengubah teks berdasarkan status kerana Pre-Project hanya ada SATU approval:

```javascript
// DIBUANG - tidak diperlukan untuk Pre-Project
const infoText = document.getElementById('approveStatusInfo');
if (status === 'Waiting for Approver 1') {
    infoText.textContent = 'Status will change to "Waiting for EPU Approval"';
} else {
    infoText.textContent = 'Status will change to next approval level';
}
```

## Workflow Pre-Project Approval

```
1. Parliament/DUN User → Create Pre-Project
   Status: "Waiting for Complete Form"
   
2. Parliament/DUN User → Submit to Approver (bila 100% complete)
   Status: "Waiting for Approver 1"
   
3. Pre-Project Approver → Approve (HANYA SATU KALI)
   Status: "Waiting for EPU Approval"
   
4. EPU → Manual Transfer to Project
   Status: "Approved"
```

## Perbezaan dengan Project Approval

| Aspek | Pre-Project | Project |
|-------|-------------|---------|
| Bilangan Approval | 1 kali | 2 kali |
| Approver | Senarai Pre-Project Approvers | Approver 1 & Approver 2 (tetap) |
| Status Akhir | "Waiting for EPU Approval" | "Approved" |
| Halaman | `/pages/pre-project` | `/pages/project` |

## Status
✅ **SELESAI** - Modal approval Pre-Project kini menunjukkan teks yang betul

## Nota
- Pre-Project approvers dikonfigurasi di `/pages/general/approver`
- Boleh pilih multiple users sebagai Pre-Project approvers
- Mana-mana approver dalam senarai boleh approve Pre-Project
- Selepas approval, Pre-Project menunggu EPU untuk transfer ke Project
