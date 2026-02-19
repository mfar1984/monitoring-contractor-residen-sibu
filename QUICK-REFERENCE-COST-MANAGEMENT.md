# Pre-Project Cost Management - Quick Reference Guide

## For Users: How It Works Now

### When NOC is Approved

**Old Behavior (WRONG ❌):**
- NOC cost went directly to `Total Cost (RM)` field
- User could edit `Total Cost (RM)` directly
- No budget control → Budget could burst

**New Behavior (CORRECT ✅):**
- NOC cost goes to `Actual Project Cost (RM)` field
- `Total Cost (RM)` is auto-calculated (READ ONLY)
- Budget is controlled and cannot exceed original

---

## Cost Fields Explained

### 1. Actual Project Cost (RM) 🔒
- **Source**: From cancelled project in NOC
- **Editable**: Yes, but cannot exceed original budget
- **Purpose**: Main project implementation cost

### 2. Consultation Cost (RM) ✏️
- **Source**: User enters
- **Editable**: Yes
- **Purpose**: Consultation services cost

### 3. LSS Inspection Cost (RM) ✏️
- **Source**: User enters
- **Editable**: Yes
- **Purpose**: LSS inspection cost

### 4. SST (RM) ✏️
- **Source**: User enters
- **Editable**: Yes
- **Purpose**: Sales and Service Tax

### 5. Others Cost (RM) ✏️
- **Source**: User enters
- **Editable**: Yes
- **Purpose**: Other miscellaneous costs

### 6. Total Cost (RM) 🔒
- **Source**: Auto-calculated
- **Editable**: NO (READ ONLY)
- **Formula**: Actual + Consultation + LSS + SST + Others

---

## Example Scenario

### Step 1: NOC Approved
```
Cancelled Project: "Old Bridge Repair"
Total Cost: RM 1,500,000
```

### Step 2: Pre-Project Created
```
Pre-Project: "Old Bridge Repair"
Actual Project Cost: RM 1,500,000 (from cancelled project)
Original Budget: RM 1,500,000 (stored for validation)
Consultation Cost: RM 0
LSS Inspection Cost: RM 0
SST: RM 0
Others Cost: RM 0
─────────────────────────────────────
Total Cost: RM 1,500,000 (auto-calculated)
```

### Step 3: User Edits Pre-Project
```
Actual Project Cost: RM 1,200,000 (reduced)
Consultation Cost: RM 100,000 (added)
LSS Inspection Cost: RM 50,000 (added)
SST: RM 72,000 (added)
Others Cost: RM 30,000 (added)
─────────────────────────────────────
Total Cost: RM 1,452,000 (auto-calculated)

Budget Status: ✅ Within budget (RM 48,000 remaining)
```

### Step 4: User Tries to Exceed Budget ❌
```
Actual Project Cost: RM 1,600,000 (exceeds original!)
─────────────────────────────────────
ERROR: "Actual Project Cost (RM 1,600,000.00) cannot exceed 
original budget of RM 1,500,000.00. This cost comes from a 
cancelled project and cannot be increased."

Action: Reduce Actual Project Cost to RM 1,500,000 or less
```

---

## Budget Rules

### ✅ ALLOWED:
- Reduce Actual Project Cost below original budget
- Add Consultation, LSS, SST, Others costs
- Total Cost can be higher than original (if other costs added)

### ❌ NOT ALLOWED:
- Increase Actual Project Cost above original budget
- Edit Total Cost directly (it's auto-calculated)
- Remove original budget limit

---

## Why These Rules?

### Budget Control
- Cost comes from CANCELLED projects
- Budget allocation is fixed by EPU
- Cannot increase beyond original allocation

### Transparency
- Clear breakdown of all costs
- Easy to see where budget is spent
- Automatic calculation prevents errors

### EPU Compliance
- EPU requires strict budget adherence
- System enforces limits automatically
- Prevents budget burst and rejection

---

## Common Questions

### Q: Can I increase Actual Project Cost?
**A**: Only if it doesn't exceed the original budget from the cancelled project.

### Q: Why can't I edit Total Cost?
**A**: Total Cost is auto-calculated to prevent errors. Edit the individual cost components instead.

### Q: What if I need more budget?
**A**: You cannot exceed the original cancelled project's budget. This is an EPU requirement.

### Q: Can Total Cost be higher than original budget?
**A**: Yes, if you add Consultation, LSS, SST, or Others costs. But Actual Project Cost cannot exceed original.

### Q: What happens if I try to exceed the budget?
**A**: The system will show an error message and prevent saving until you reduce the cost.

---

## For Developers: Technical Details

### Database Fields:
- `actual_project_cost` DECIMAL(15,2) - Main project cost
- `consultation_cost` DECIMAL(15,2) - Consultation cost
- `lss_inspection_cost` DECIMAL(15,2) - LSS inspection cost
- `sst` DECIMAL(15,2) - Sales and Service Tax
- `others_cost` DECIMAL(15,2) - Other costs
- `total_cost` DECIMAL(15,2) - Auto-calculated sum
- `original_project_cost` DECIMAL(15,2) - Budget limit

### Model Methods:
```php
$preProject->calculateTotalCost(); // Returns sum of all costs
$preProject->isWithinBudget(); // Returns true if within budget
$preProject->getBudgetDifference(); // Returns remaining/exceeded amount
```

### Validation:
```php
// In PageController::preProjectUpdate()
if ($request->actual_project_cost > $preProject->original_project_cost) {
    return redirect()->back()->withErrors([...]);
}
```

---

## Summary

The new cost management system:
- ✅ Protects budget from cancelled projects
- ✅ Auto-calculates Total Cost (no manual errors)
- ✅ Enforces EPU budget compliance
- ✅ Provides clear cost breakdown
- ✅ Prevents budget burst

**Result**: Better budget control, EPU compliance, and transparent cost tracking.

---

**Last Updated**: February 17, 2026
