# NOC Budget Edit Fix

## Problem Statement

When editing a pre-project that has been included in a NOC (status = "NOC"), the "Available for this project" field was showing the wrong amount (RM 5,000,000 instead of the locked Actual Project Cost).

## Root Cause

The JavaScript budget calculation logic did not differentiate between:
1. Pre-projects that are NOT in NOC (can use remaining budget + original cost)
2. Pre-projects that ARE in NOC (locked to Actual Project Cost only)

## Solution Implemented

### 1. Modified `editPreProject()` Function

**Changes:**
- Added detection for NOC status: `const isNocProject = data.status === 'NOC'`
- Store NOC status in dataset: `totalCostDisplay.dataset.isNocProject`
- Store original Actual Project Cost: `totalCostDisplay.dataset.originalActualCost`
- Make all cost fields READ-ONLY for NOC projects
- Add grey background to indicate read-only state

**Code:**
```javascript
// Check if this is a NOC project
const isNocProject = data.status === 'NOC';

// Store in dataset
totalCostDisplay.dataset.originalActualCost = data.actual_project_cost || 0;
totalCostDisplay.dataset.isNocProject = isNocProject ? 'true' : 'false';

// If NOC project, make all cost fields READ-ONLY
if (isNocProject) {
    document.getElementById('actual_project_cost').readOnly = true;
    document.getElementById('consultation_cost').readOnly = true;
    document.getElementById('lss_inspection_cost').readOnly = true;
    document.getElementById('sst').readOnly = true;
    document.getElementById('others_cost').readOnly = true;
    
    // Add visual indicator (grey background)
    document.getElementById('actual_project_cost').style.backgroundColor = '#f5f5f5';
    // ... (same for other fields)
}
```

### 2. Modified `updateBudgetForYear()` Function

**Changes:**
- Check if project is NOC
- For NOC projects: Show "Available for this project" = Actual Project Cost ONLY (LOCKED)
- For non-NOC projects: Show "Available for this project" = Remaining + Original cost
- Add visual indicator "(LOCKED - NOC Project)" for NOC projects

**Code:**
```javascript
if (isEditMode) {
    if (isNocProject) {
        // NOC Project: Show Actual Project Cost ONLY (LOCKED)
        const originalActualCost = parseFloat(totalCostDisplay.dataset.originalActualCost) || 0;
        budgetText.innerHTML = 'Available for this project: RM <span id="budget-amount">' + 
            originalActualCost.toFixed(2) + 
            '</span> <span style="color: #dc3545; font-size: 10px;">(LOCKED - NOC Project)</span>';
    } else {
        // Non-NOC Project: Show Remaining + Original cost
        let availableBudget = parseFloat(data.remaining_budget || 0);
        if (selectedYear === originalYear || !originalYear || originalYear === '') {
            availableBudget += originalCost;
        }
        budgetText.innerHTML = 'Available for this project: RM <span id="budget-amount">' + 
            availableBudget.toFixed(2) + '</span>';
    }
}
```

### 3. Modified `updateBudgetReminder()` Function

**Changes:**
- Check if project is NOC
- For NOC projects: Available budget = Actual Project Cost ONLY (cannot change)
- For non-NOC projects: Available budget = Remaining + Original cost
- Show appropriate error message for NOC projects

**Code:**
```javascript
if (isEditMode) {
    if (isNocProject) {
        // NOC Project: Available = Actual Project Cost ONLY (KEKAL, tidak boleh ubah)
        availableBudget = originalActualCost;
    } else {
        // Non-NOC Project: Available = Remaining + Original cost
        if (selectedYear === originalYear || !originalYear || originalYear === '') {
            availableBudget = remainingBudget + originalCost;
        }
    }
}
```

## Requirements Summary

### 1. Create Pre-Project (NEW)
- Show "Remaining budget" from Parliament/DUN
- Example: Remaining = RM 300,000, user can create up to RM 300,000

### 2. Edit Pre-Project (NOT in NOC)
- Status: NOT "NOC" (e.g., "Waiting for Complete Form", "Active")
- Show "Available for this project" = Remaining budget + Original cost
- User can increase or decrease within this limit
- Example: Remaining RM 4,700,000 + Original RM 100,000 = Available RM 4,800,000

### 3. Edit Pre-Project (IN NOC) ⭐ CRITICAL
- Status: "NOC"
- Show "Available for this project" = Actual Project Cost ONLY
- Example: If Actual Project Cost = RM 100,000
- Available = RM 100,000 (FIXED - cannot be less, cannot be more)
- All cost fields are READ-ONLY (grey background)
- User can edit other fields (name, location, etc.) but NOT cost fields

## Testing Scenarios

### Scenario 1: Create New Pre-Project
1. Click "Create Pre-Project"
2. Select year
3. Budget reminder should show "Remaining budget: RM X"
4. User can enter cost up to remaining budget

### Scenario 2: Edit Pre-Project (NOT in NOC)
1. Edit a pre-project with status "Waiting for Complete Form"
2. Budget reminder should show "Available for this project: RM X" (Remaining + Original)
3. User can change cost within available budget
4. Cost fields are EDITABLE

### Scenario 3: Edit Pre-Project (IN NOC)
1. Edit a pre-project with status "NOC"
2. Budget reminder should show "Available for this project: RM X (LOCKED - NOC Project)"
3. X = Actual Project Cost from the pre-project
4. All cost fields are READ-ONLY (grey background)
5. User CANNOT change any cost values
6. User CAN edit other fields (name, location, etc.)

## Files Modified

1. `resources/views/pages/pre-project.blade.php`
   - Modified `editPreProject()` function
   - Modified `updateBudgetForYear()` function
   - Modified `updateBudgetReminder()` function

## Database Fields Used

- `pre_projects.status` - To check if project is "NOC"
- `pre_projects.actual_project_cost` - The locked amount for NOC projects
- `pre_projects.total_cost` - The total cost for non-NOC projects
- `pre_projects.project_year` - To determine if same year

## Visual Indicators

1. **NOC Projects:**
   - Cost fields have grey background (#f5f5f5)
   - Cost fields are read-only (cannot type)
   - Budget text shows "(LOCKED - NOC Project)" in red

2. **Non-NOC Projects:**
   - Cost fields have white background
   - Cost fields are editable
   - Budget text shows normal "Available for this project"

## Implementation Complete ✅

All requirements have been implemented:
- ✅ Create mode shows "Remaining budget"
- ✅ Edit mode (non-NOC) shows "Available for this project" = Remaining + Original
- ✅ Edit mode (NOC) shows "Available for this project" = Actual Project Cost (LOCKED)
- ✅ NOC projects have READ-ONLY cost fields
- ✅ Visual indicators for NOC projects
- ✅ Proper budget validation for all scenarios
