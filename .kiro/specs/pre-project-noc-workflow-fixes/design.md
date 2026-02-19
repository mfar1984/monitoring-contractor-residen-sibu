# Design Document

## Overview

This design document specifies the implementation details for correcting the Pre-Project approval workflow and NOC budget validation. The corrections ensure that Pre-Projects follow a single-approval workflow (distinct from Projects' two-approval workflow) and that NOCs can only be created when budget is fully allocated with no empty rows.

## Architecture

### System Components

1. **PageController** - Approval and rejection methods for Pre-Projects
2. **Pre-Project List View** - Approval modal and status display
3. **NOC Create View** - Budget validation and button state management
4. **JavaScript Validation** - Real-time budget calculation and empty row detection

### Data Flow

#### Pre-Project Approval Flow
```
User clicks "Approve" on Pre-Project
    ↓
Controller checks user is in Pre-Project Approvers list
    ↓
Controller validates status is "Waiting for Approver 1"
    ↓
Controller updates status to "Waiting for EPU Approval" (skip Approver 2)
    ↓
Record approval timestamp and user ID
    ↓
Redirect with success message
```

#### NOC Budget Validation Flow
```
User enters New Cost value
    ↓
JavaScript calculates Total Allocated Budget
    ↓
JavaScript calculates Remaining Budget
    ↓
JavaScript checks for empty rows
    ↓
JavaScript determines validation state (priority order):
  1. Over budget? → Show error, disable button
  2. Has empty rows? → Show warning, disable button
  3. Has remaining budget? → Show info, disable button
  4. All valid? → Enable button
    ↓
Update UI with validation message and button state
```

## Components and Interfaces

### 1. Pre-Project Approval Controller Method

**Location**: `app/Http/Controllers/Pages/PageController.php`

**Modified Method**: `preProjectApprove()`

```php
public function preProjectApprove(Request $request, $id)
{
    $preProject = PreProject::findOrFail($id);
    $user = auth()->user();
    
    // Get Pre-Project approvers from settings
    $approversSetting = IntegrationSetting::where('type', 'approver')
        ->where('key', 'pre_project_approvers')
        ->first();
    
    if (!$approversSetting) {
        return redirect()->back()->with('error', 'Pre-Project approvers not configured');
    }
    
    $approverIds = json_decode($approversSetting->value, true);
    
    // Check if current user is in approvers list
    if (!in_array($user->id, $approverIds)) {
        return redirect()->back()->with('error', 'You are not authorized to approve Pre-Projects');
    }
    
    // Check status - must be "Waiting for Approver 1"
    if ($preProject->status !== 'Waiting for Approver 1') {
        return redirect()->back()->with('error', 'This Pre-Project cannot be approved at this stage');
    }
    
    // Update status directly to "Waiting for EPU Approval" (skip Approver 2)
    $preProject->update([
        'status' => 'Waiting for EPU Approval',
        'approved_by' => $user->id,
        'approved_at' => now(),
        'approval_remarks' => $request->remarks,
    ]);
    
    return redirect()->route('pages.pre-project')
        ->with('success', 'Pre-Project approved successfully');
}
```

**Modified Method**: `preProjectReject()`

```php
public function preProjectReject(Request $request, $id)
{
    $preProject = PreProject::findOrFail($id);
    $user = auth()->user();
    
    // Get Pre-Project approvers from settings
    $approversSetting = IntegrationSetting::where('type', 'approver')
        ->where('key', 'pre_project_approvers')
        ->first();
    
    if (!$approversSetting) {
        return redirect()->back()->with('error', 'Pre-Project approvers not configured');
    }
    
    $approverIds = json_decode($approversSetting->value, true);
    
    // Check if current user is in approvers list
    if (!in_array($user->id, $approverIds)) {
        return redirect()->back()->with('error', 'You are not authorized to reject Pre-Projects');
    }
    
    // Check status - must be "Waiting for Approver 1" (NOT Approver 2)
    if ($preProject->status !== 'Waiting for Approver 1') {
        return redirect()->back()->with('error', 'This Pre-Project cannot be rejected at this stage');
    }
    
    // Update status to "Rejected"
    $preProject->update([
        'status' => 'Rejected',
        'rejected_by' => $user->id,
        'rejected_at' => now(),
        'rejection_remarks' => $request->remarks,
    ]);
    
    return redirect()->route('pages.pre-project')
        ->with('success', 'Pre-Project rejected successfully');
}
```

### 2. Pre-Project Approval Modal

**Location**: `resources/views/pages/pre-project.blade.php`

**Modal HTML** (simplified, no dynamic text):

```blade
<!-- Approve Modal -->
<div id="approveModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Approve Pre-Project</h3>
            <button type="button" class="close-modal" onclick="closeApproveModal()">
                <i class="material-symbols-outlined">close</i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to approve this Pre-Project?</p>
            <ul style="margin: 15px 0; padding-left: 20px; color: #666;">
                <li>Pre-Project: <strong id="approvePreProjectName"></strong></li>
                <li>Status will change to "Waiting for EPU Approval"</li>
            </ul>
            <form id="approveForm" method="POST">
                @csrf
                <div class="form-group">
                    <label for="approveRemarks">Remarks (Optional)</label>
                    <textarea id="approveRemarks" name="remarks" rows="3" 
                              placeholder="Enter approval remarks..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitApprove()">Approve</button>
        </div>
    </div>
</div>
```

**JavaScript** (no dynamic text changes):

```javascript
let currentApproveId = null;

function showApproveModal(id, name) {
    currentApproveId = id;
    document.getElementById('approvePreProjectName').textContent = name;
    document.getElementById('approveForm').action = `/pages/pre-project/${id}/approve`;
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    currentApproveId = null;
    document.getElementById('approveModal').style.display = 'none';
    document.getElementById('approveRemarks').value = '';
}

function submitApprove() {
    document.getElementById('approveForm').submit();
}
```

### 3. NOC Budget Validation JavaScript

**Location**: `resources/views/pages/project-noc-create.blade.php`

**Budget Calculation Function**:

```javascript
function updateBudgetSummary() {
    // Calculate Total NOC Budget (sum of all Kos Asal)
    let totalNocBudget = 0;
    document.querySelectorAll('.kos-asal-input').forEach(input => {
        totalNocBudget += parseFloat(input.value) || 0;
    });
    
    // Calculate Total Allocated Budget (sum of all Kos Baru)
    let totalAllocated = 0;
    document.querySelectorAll('.kos-baru-input').forEach(input => {
        totalAllocated += parseFloat(input.value) || 0;
    });
    
    // Calculate Remaining Budget
    const remaining = totalNocBudget - totalAllocated;
    
    // Update display
    document.getElementById('totalNocBudget').textContent = formatCurrency(totalNocBudget);
    document.getElementById('totalAllocated').textContent = formatCurrency(totalAllocated);
    document.getElementById('remainingBudget').textContent = formatCurrency(remaining);
    
    // Update remaining budget color
    const remainingElement = document.getElementById('remainingBudget');
    if (remaining < 0) {
        remainingElement.style.color = '#dc3545'; // Red
    } else {
        remainingElement.style.color = '#333'; // Black
    }
    
    // Validate and update button state
    validateBudgetAndUpdateButton(remaining);
}

function formatCurrency(amount) {
    return 'RM ' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
```

**Empty Row Detection Function**:

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

**Validation and Button State Function**:

```javascript
function validateBudgetAndUpdateButton(remaining) {
    const submitBtn = document.getElementById('createNocBtn');
    const warningDiv = document.getElementById('budgetWarning');
    const infoDiv = document.getElementById('budgetInfo');
    const emptyRowWarning = document.getElementById('emptyRowWarning');
    
    // Hide all messages initially
    warningDiv.style.display = 'none';
    infoDiv.style.display = 'none';
    emptyRowWarning.style.display = 'none';
    
    // Check for empty rows
    const hasEmptyRows = checkForEmptyRows();
    
    // Priority 1: Over budget (highest priority)
    if (remaining < 0) {
        warningDiv.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }
    
    // Priority 2: Empty rows detected
    if (hasEmptyRows) {
        emptyRowWarning.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }
    
    // Priority 3: Budget not fully allocated
    if (remaining > 0) {
        infoDiv.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }
    
    // All validations passed: remaining = 0 AND no empty rows
    submitBtn.disabled = false;
}
```

### 4. NOC Validation Messages HTML

**Location**: `resources/views/pages/project-noc-create.blade.php`

**Budget Exceeded Message** (Priority 1):

```html
<!-- Budget Exceeded Warning -->
<div id="budgetWarning" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #dc3545; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
    <div style="display: flex; align-items: flex-start; gap: 10px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #dc3545; flex-shrink: 0; margin-top: 1px;">error</span>
        <div style="line-height: 1.5;">
            <strong style="display: block; margin-bottom: 4px; color: #333;">Budget Exceeded</strong>
            <span style="color: #666;">Total allocated budget exceeds the NOC budget. Please adjust the New Cost values.</span>
        </div>
    </div>
</div>
```

**Empty Rows Warning** (Priority 2):

```html
<!-- Empty Row Warning -->
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

**Budget Not Fully Allocated Info** (Priority 3):

```html
<!-- Budget Not Fully Allocated Info -->
<div id="budgetInfo" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #ffc107; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
    <div style="display: flex; align-items: flex-start; gap: 10px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #ffc107; flex-shrink: 0; margin-top: 1px;">info</span>
        <div style="line-height: 1.5;">
            <strong style="display: block; margin-bottom: 4px; color: #333;">Budget Not Fully Allocated</strong>
            <span style="color: #666;">Please allocate all remaining budget before creating NOC.</span>
        </div>
    </div>
</div>
```

### 5. Pre-Project Status Badge Display

**Location**: `resources/views/pages/pre-project.blade.php`

**Status Badge Logic**:

```blade
<td>
    @if($preProject->status === 'Waiting for Complete Form')
        <span class="status-badge" style="background-color: #f5f5f5; color: #666;">
            Waiting for Complete Form
        </span>
    @elseif($preProject->status === 'Waiting for Approver 1')
        <span class="status-badge" style="background-color: #fff3cd; color: #856404;">
            Waiting for Approver 1
        </span>
    @elseif($preProject->status === 'Waiting for EPU Approval')
        <span class="status-badge" style="background-color: #cce5ff; color: #004085;">
            Waiting for EPU Approval
        </span>
    @elseif($preProject->status === 'Approved')
        <span class="status-badge status-active">
            Approved
        </span>
    @elseif($preProject->status === 'Rejected')
        <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">
            Rejected
        </span>
    @endif
</td>
```

**Note**: No "Waiting for Approver 2" status badge for Pre-Projects.

### 6. Completeness Percentage Display Logic

**Location**: `resources/views/pages/pre-project.blade.php`

**Completeness Column**:

```blade
<td>
    @if(in_array($preProject->status, ['Waiting for Complete Form', 'Waiting for EPU Approval']))
        <span class="status-badge" style="background-color: {{ $preProject->completeness_color }}; color: white;">
            {{ $preProject->completeness_percentage }}%
        </span>
    @else
        <span style="color: #999;">N/A</span>
    @endif
</td>
```

## Data Models

### PreProject Model

**Existing Fields** (no schema changes needed):

- `status` - Current status (Waiting for Complete Form, Waiting for Approver 1, Waiting for EPU Approval, Approved, Rejected)
- `approved_by` - User ID who approved (nullable)
- `approved_at` - Approval timestamp (nullable)
- `approval_remarks` - Approval remarks (nullable)
- `rejected_by` - User ID who rejected (nullable)
- `rejected_at` - Rejection timestamp (nullable)
- `rejection_remarks` - Rejection remarks (nullable)

### NOC Model

**Existing Fields** (no schema changes needed):

- `noc_number` - Unique NOC identifier
- `parliament_id` - Foreign key to parliaments
- `dun_id` - Foreign key to duns
- `noc_date` - Date of NOC
- `status` - Current status (Draft, Submitted, Approved, Rejected)

### NOC Project Pivot Table

**Existing Fields** (no schema changes needed):

- `noc_id` - Foreign key to nocs
- `project_id` - Foreign key to projects
- `tahun_rtp` - RTP Year
- `no_projek` - Project Number
- `kos_asal` - Original Cost (Current Cost)
- `kos_baru` - New Cost (nullable)
- `noc_note_id` - Foreign key to noc_notes

## Correctness Properties

### Property 1: Pre-Project Single Approval Transition

*For any* Pre-Project with status "Waiting for Approver 1", when approved by an authorized approver, the status should change directly to "Waiting for EPU Approval" without passing through "Waiting for Approver 2".

**Validates: Requirements 1.1, 1.2**

### Property 2: Pre-Project Approval Authorization

*For any* Pre-Project approval attempt, the system should allow the action if and only if the current user's ID is in the Pre-Project Approvers list configured in settings.

**Validates: Requirements 1.1**

### Property 3: Pre-Project Rejection Status Check

*For any* Pre-Project rejection attempt, the system should allow the action if and only if the current status is "Waiting for Approver 1" (not "Waiting for Approver 2").

**Validates: Requirements 1.6**

### Property 4: NOC Button Disabled When Budget Remaining

*For any* NOC creation form, the "Create NOC" button should be disabled if and only if the Remaining Budget is not equal to RM 0.00 OR there are empty rows in the projects table.

**Validates: Requirements 3.1, 3.3, 4.2**

### Property 5: NOC Empty Row Definition

*For any* row in the NOC projects table, the row should be considered empty if and only if the New Cost (Kos Baru) value is 0 or null.

**Validates: Requirements 4.1**

### Property 6: NOC Validation Message Priority

*For any* NOC creation form with multiple validation issues, the system should display only the highest priority message according to the order: Budget Exceeded > Empty Rows Detected > Budget Not Fully Allocated.

**Validates: Requirements 6.1, 6.2, 6.3**

### Property 7: NOC Budget Calculation Accuracy

*For any* NOC creation form, the Remaining Budget should equal the Total NOC Budget (sum of all Kos Asal) minus the Total Allocated Budget (sum of all Kos Baru).

**Validates: Requirements 3.4, 7.1**

### Property 8: NOC Remaining Budget Color

*For any* NOC creation form, the Remaining Budget should be displayed in red color if and only if the value is less than RM 0.00.

**Validates: Requirements 5.3, 7.2, 7.3**

### Property 9: Pre-Project Approval Modal Static Text

*For any* Pre-Project approval modal display, the status change text should always be "Status will change to 'Waiting for EPU Approval'" regardless of the current Pre-Project status.

**Validates: Requirements 1.4, 1.5, 8.1, 8.2, 8.3**

### Property 10: Pre-Project Status Badge Exclusion

*For any* Pre-Project list display, the system should never display a "Waiting for Approver 2" status badge.

**Validates: Requirements 1.3, 9.2**

### Property 11: Completeness Display Conditions

*For any* Pre-Project in the list, the completeness percentage should be displayed if and only if the status is "Waiting for Complete Form" or "Waiting for EPU Approval".

**Validates: Requirements 10.1, 10.2, 10.3**

### Property 12: NOC Real-Time Budget Update

*For any* change to a New Cost (Kos Baru) input field in the NOC creation form, the system should immediately recalculate and update the Total Allocated Budget, Remaining Budget, and validation state.

**Validates: Requirements 3.5, 7.4**

## Error Handling

### Pre-Project Approval Errors

**Unauthorized Approver**:
- Error: User not in Pre-Project Approvers list attempts to approve
- Handling: Return error message "You are not authorized to approve Pre-Projects"
- User Action: Contact administrator to be added to approvers list

**Invalid Status for Approval**:
- Error: User attempts to approve Pre-Project not in "Waiting for Approver 1" status
- Handling: Return error message "This Pre-Project cannot be approved at this stage"
- User Action: Check Pre-Project status, refresh page

**Approvers Not Configured**:
- Error: Pre-Project Approvers setting not found in database
- Handling: Return error message "Pre-Project approvers not configured"
- User Action: Contact administrator to configure approvers in settings

### NOC Budget Validation Errors

**Over Budget**:
- Error: Total Allocated Budget exceeds Total NOC Budget
- Handling: Display red error message "Budget Exceeded", disable Create NOC button
- User Action: Reduce New Cost values to bring budget within limits

**Empty Rows Detected**:
- Error: One or more rows in projects table have no New Cost value
- Handling: Display red warning message "Empty Rows Detected", disable Create NOC button
- User Action: Delete empty rows using the delete button

**Budget Not Fully Allocated**:
- Error: Remaining Budget is greater than RM 0.00
- Handling: Display yellow info message "Budget Not Fully Allocated", disable Create NOC button
- User Action: Increase New Cost values or add more projects to allocate remaining budget

## Testing Strategy

### Unit Tests

**Pre-Project Approval Tests**:

```php
public function test_pre_project_approval_skips_approver_2()
{
    $approver = User::factory()->create();
    IntegrationSetting::create([
        'type' => 'approver',
        'key' => 'pre_project_approvers',
        'value' => json_encode([$approver->id])
    ]);
    
    $preProject = PreProject::factory()->create([
        'status' => 'Waiting for Approver 1'
    ]);
    
    $this->actingAs($approver);
    $response = $this->post(route('pages.pre-project.approve', $preProject->id), [
        'remarks' => 'Approved'
    ]);
    
    $preProject->refresh();
    $this->assertEquals('Waiting for EPU Approval', $preProject->status);
    $this->assertNotEquals('Waiting for Approver 2', $preProject->status);
}

public function test_unauthorized_user_cannot_approve_pre_project()
{
    $unauthorizedUser = User::factory()->create();
    $preProject = PreProject::factory()->create([
        'status' => 'Waiting for Approver 1'
    ]);
    
    $this->actingAs($unauthorizedUser);
    $response = $this->post(route('pages.pre-project.approve', $preProject->id));
    
    $response->assertSessionHas('error', 'You are not authorized to approve Pre-Projects');
    $preProject->refresh();
    $this->assertEquals('Waiting for Approver 1', $preProject->status);
}
```

**NOC Budget Validation Tests**:

```php
public function test_noc_button_disabled_when_budget_remaining()
{
    $this->actingAs(User::factory()->create(['parliament_category_id' => 1]));
    $response = $this->get(route('pages.project.noc.create'));
    
    $response->assertSee('createNocBtn');
    $response->assertSee('disabled');
}

public function test_noc_button_enabled_when_budget_fully_allocated()
{
    // This would be tested via JavaScript/browser testing
    // Unit test can verify the HTML structure is correct
    $this->actingAs(User::factory()->create(['parliament_category_id' => 1]));
    $response = $this->get(route('pages.project.noc.create'));
    
    $response->assertSee('createNocBtn');
    $response->assertSee('validateBudgetAndUpdateButton');
}
```

### Integration Tests

**Complete Pre-Project Approval Workflow**:

1. Create Pre-Project with status "Waiting for Approver 1"
2. Attempt approval by unauthorized user → fails
3. Attempt approval by authorized approver → succeeds
4. Verify status changed to "Waiting for EPU Approval" (not "Waiting for Approver 2")
5. Verify approval timestamp and user ID recorded
6. Verify approval modal shows correct static text

**Complete NOC Budget Validation Workflow**:

1. Open NOC creation page
2. Import projects → Total NOC Budget calculated
3. Enter New Cost values → Total Allocated Budget calculated
4. Verify Remaining Budget = Total NOC Budget - Total Allocated Budget
5. Test over budget scenario → button disabled, red error shown
6. Test empty rows scenario → button disabled, red warning shown
7. Test remaining budget scenario → button disabled, yellow info shown
8. Allocate all budget (remaining = 0) and delete empty rows → button enabled
9. Verify only one validation message shown at a time (highest priority)

### Browser/JavaScript Tests

**NOC Real-Time Validation**:

- Test budget calculation updates on input change
- Test empty row detection updates on row deletion
- Test validation message priority switching
- Test button state changes based on validation
- Test currency formatting
- Test color changes for remaining budget

## Implementation Notes

### No Database Migrations Required

All required database fields already exist:
- Pre-Project approval fields (approved_by, approved_at, approval_remarks)
- Pre-Project rejection fields (rejected_by, rejected_at, rejection_remarks)
- NOC project pivot table fields (kos_asal, kos_baru)

### JavaScript Event Listeners

Add event listeners to trigger validation on:
- Input change in New Cost fields
- Row deletion
- Row addition
- Page load

```javascript
// Add event listeners to all New Cost inputs
document.querySelectorAll('.kos-baru-input').forEach(input => {
    input.addEventListener('input', updateBudgetSummary);
});

// Add event listener to delete buttons
document.querySelectorAll('.delete-row-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Delete row logic
        updateBudgetSummary(); // Recalculate after deletion
    });
});

// Calculate on page load
document.addEventListener('DOMContentLoaded', updateBudgetSummary);
```

### Performance Considerations

**Budget Calculation**:
- Calculation is performed in-memory (no database queries)
- O(n) complexity where n = number of project rows
- Acceptable performance for typical NOC size (< 100 projects)

**Empty Row Detection**:
- O(n) complexity where n = number of project rows
- Runs on every input change (acceptable for typical NOC size)

### Security Considerations

**Authorization**:
- Pre-Project approval: Check user ID against approvers list
- NOC creation: Check user has parliament_id or dun_id
- Server-side validation required in addition to client-side

**Input Validation**:
- Validate New Cost values are numeric and non-negative
- Validate budget calculations on server-side before NOC creation
- Prevent SQL injection in remarks fields

### Accessibility

**Validation Messages**:
- Use semantic HTML (proper heading levels)
- Include ARIA labels for screen readers
- Ensure sufficient color contrast
- Don't rely solely on color (use icons and text)

**Button States**:
- Disabled buttons should have aria-disabled attribute
- Provide clear visual feedback for disabled state
- Include tooltip or message explaining why button is disabled

### Browser Compatibility

**JavaScript**:
- Use ES6+ features (arrow functions, template literals)
- Compatible with modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation for older browsers

**CSS**:
- Use flexbox for layout
- Standard CSS properties (no experimental features)
- Cross-browser tested

