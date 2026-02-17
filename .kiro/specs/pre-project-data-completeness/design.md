# Design Document

## Overview

The Pre-Project Data Completeness feature adds visual indicators, validation logic, and submission controls to ensure Pre-Projects contain complete data before EPU submission. The system calculates completeness percentage based on required fields, displays color-coded indicators, validates data before submission, and manages the status transition from "Waiting for EPU Approval" to "Submitted to EPU".

This feature integrates with the existing Pre-Project approval workflow and extends the PreProject model with completeness calculation methods.

## Architecture

### System Components

1. **PreProject Model** - Extended with completeness calculation methods
2. **PageController** - New submission endpoint and validation logic
3. **Pre-Project List View** - Enhanced with completeness column and Submit button
4. **Validation Modal** - Displays missing required fields
5. **Database** - Existing pre_projects table (no schema changes needed)

### Data Flow

```
User clicks "Submit to EPU"
    ↓
Controller validates completeness
    ↓
If incomplete → Show modal with missing fields
    ↓
If complete → Update status to "Submitted to EPU"
    ↓
Record submission timestamp
    ↓
Redirect with success message
```

## Components and Interfaces

### 1. PreProject Model Extensions

**Location**: `app/Models/PreProject.php`

**New Methods**:

```php
/**
 * Get the list of required fields for EPU submission
 * 
 * @return array Array of field names and their display labels
 */
public function getRequiredFieldsDefinition(): array
{
    return [
        'project_scope' => 'Project Scope',
        'project_category_id' => 'Project Category',
        'implementation_period' => 'Implementation Period',
        'division_id' => 'Division',
        'district_id' => 'District',
        'land_title_status_id' => 'Land Title Status',
        'implementing_agency_id' => 'Implementing Agency',
        'implementation_method_id' => 'Implementation Method',
        'project_ownership_id' => 'Project Ownership',
    ];
}

/**
 * Calculate data completeness percentage
 * 
 * @return int Percentage from 0 to 100
 */
public function getCompletenessPercentage(): int
{
    $requiredFields = array_keys($this->getRequiredFieldsDefinition());
    $totalFields = count($requiredFields);
    
    if ($totalFields === 0) {
        return 100;
    }
    
    $filledFields = 0;
    foreach ($requiredFields as $field) {
        if (!empty($this->$field)) {
            $filledFields++;
        }
    }
    
    return (int) round(($filledFields / $totalFields) * 100);
}

/**
 * Get array of missing required fields with display names
 * 
 * @return array Array of missing field display names
 */
public function getMissingRequiredFields(): array
{
    $requiredFields = $this->getRequiredFieldsDefinition();
    $missingFields = [];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($this->$field)) {
            $missingFields[] = $label;
        }
    }
    
    return $missingFields;
}

/**
 * Check if Pre-Project data is complete for EPU submission
 * 
 * @return bool True if all required fields are filled
 */
public function isDataComplete(): bool
{
    return $this->getCompletenessPercentage() === 100;
}

/**
 * Get completeness badge color based on percentage
 * 
 * @return string CSS color code
 */
public function getCompletenessBadgeColor(): string
{
    $percentage = $this->getCompletenessPercentage();
    
    if ($percentage >= 81) {
        return '#28a745'; // Green
    } elseif ($percentage >= 51) {
        return '#ffc107'; // Yellow
    } else {
        return '#dc3545'; // Red
    }
}
```

### 2. PageController Extensions

**Location**: `app/Http/Controllers/Pages/PageController.php`

**New Method**:

```php
/**
 * Submit Pre-Project to EPU
 * 
 * @param int $id Pre-Project ID
 * @return \Illuminate\Http\RedirectResponse
 */
public function preProjectSubmitToEpu($id)
{
    $preProject = \App\Models\PreProject::findOrFail($id);
    $user = auth()->user();
    
    // Authorization: Only Member of Parliament users can submit
    if (!$user->parliament_category_id && !$user->dun_basic_id) {
        return redirect()->back()->with('error', 'You are not authorized to submit Pre-Projects to EPU');
    }
    
    // Status check: Must be "Waiting for EPU Approval"
    if ($preProject->status !== 'Waiting for EPU Approval') {
        return redirect()->back()->with('error', 'This Pre-Project cannot be submitted at this stage');
    }
    
    // Validate data completeness
    if (!$preProject->isDataComplete()) {
        $missingFields = $preProject->getMissingRequiredFields();
        return redirect()->back()
            ->with('error', 'Pre-Project data is incomplete')
            ->with('missing_fields', $missingFields);
    }
    
    // Update status to "Submitted to EPU"
    $preProject->update([
        'status' => 'Submitted to EPU',
        'submitted_to_epu_at' => now(),
        'submitted_to_epu_by' => $user->id,
    ]);
    
    return redirect()->route('pages.pre-project')
        ->with('success', 'Pre-Project submitted to EPU successfully');
}
```

**Modified Method**: `preProject()`

Add completeness data to view:

```php
public function preProject(): View
{
    $user = auth()->user();
    
    $preProjects = \App\Models\PreProject::with([
        'residenCategory', 
        'agencyCategory', 
        'parliament', 
        'projectCategory',
        'division',
        'district',
        'parliamentLocation',
        'dun',
        'landTitleStatus',
        'implementingAgency',
        'implementationMethod',
        'projectOwnership'
    ])->orderBy('created_at', 'desc')->get();
    
    // Add completeness data to each pre-project
    foreach ($preProjects as $preProject) {
        $preProject->completeness_percentage = $preProject->getCompletenessPercentage();
        $preProject->completeness_color = $preProject->getCompletenessBadgeColor();
    }
    
    // ... rest of existing code
}
```

### 3. Route Definition

**Location**: `routes/web.php`

**New Route**:

```php
Route::post('/pages/pre-project/{id}/submit-to-epu', [PageController::class, 'preProjectSubmitToEpu'])
    ->name('pages.pre-project.submit-to-epu');
```

### 4. Pre-Project List View Enhancement

**Location**: `resources/views/pages/pre-project.blade.php`

**Table Header Addition**:

Add "Completeness" column after "Status" column:

```blade
<th>Status</th>
<th>Completeness</th>
<th>Actions</th>
```

**Table Body Addition**:

Display completeness indicator:

```blade
<td>
    @if($preProject->status === 'Waiting for EPU Approval')
        <span class="status-badge" style="background-color: {{ $preProject->completeness_color }}; color: white;">
            {{ $preProject->completeness_percentage }}%
        </span>
    @else
        <span style="color: #999;">N/A</span>
    @endif
</td>
```

**Actions Column Modification**:

Replace Edit/Delete buttons with Submit button for "Waiting for EPU Approval" status:

```blade
<td>
    @if($preProject->status === 'Waiting for EPU Approval' && ($user->parliament_category_id || $user->dun_basic_id))
        <!-- Submit to EPU Button -->
        <form method="POST" action="{{ route('pages.pre-project.submit-to-epu', $preProject->id) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm" title="Submit to EPU">
                <i class="material-symbols-outlined" style="font-size: 16px;">send</i>
                Submit to EPU
            </button>
        </form>
    @elseif($preProject->status === 'Waiting for Approval')
        <!-- Existing Edit/Delete buttons -->
        <button class="btn btn-sm btn-primary" onclick="editPreProject({{ $preProject->id }})" title="Edit">
            <i class="material-symbols-outlined" style="font-size: 16px;">edit</i>
        </button>
        <button class="btn btn-sm btn-danger" onclick="deletePreProject({{ $preProject->id }})" title="Delete">
            <i class="material-symbols-outlined" style="font-size: 16px;">delete</i>
        </button>
    @endif
    
    <!-- Print button always visible -->
    <a href="{{ route('pages.pre-project.print', $preProject->id) }}" target="_blank" class="btn btn-sm btn-secondary" title="Print">
        <i class="material-symbols-outlined" style="font-size: 16px;">print</i>
    </a>
</td>
```

### 5. Missing Fields Modal

**Location**: `resources/views/pages/pre-project.blade.php`

**Modal HTML**:

```blade
<!-- Missing Fields Modal -->
<div id="missingFieldsModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Incomplete Data</h3>
            <button type="button" class="close-modal" onclick="closeMissingFieldsModal()">
                <i class="material-symbols-outlined">close</i>
            </button>
        </div>
        <div class="modal-body">
            <p>The following required fields are missing:</p>
            <ul id="missingFieldsList" style="margin-left: 20px; color: #dc3545;">
                <!-- Missing fields will be populated here -->
            </ul>
            <p style="margin-top: 15px;">Please complete all required fields before submitting to EPU.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMissingFieldsModal()">Close</button>
        </div>
    </div>
</div>
```

**JavaScript**:

```javascript
// Show missing fields modal if validation fails
@if(session('missing_fields'))
    document.addEventListener('DOMContentLoaded', function() {
        showMissingFieldsModal(@json(session('missing_fields')));
    });
@endif

function showMissingFieldsModal(missingFields) {
    const modal = document.getElementById('missingFieldsModal');
    const list = document.getElementById('missingFieldsList');
    
    // Clear existing list
    list.innerHTML = '';
    
    // Populate missing fields
    missingFields.forEach(field => {
        const li = document.createElement('li');
        li.textContent = field;
        list.appendChild(li);
    });
    
    modal.style.display = 'flex';
}

function closeMissingFieldsModal() {
    document.getElementById('missingFieldsModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('missingFieldsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeMissingFieldsModal();
    }
});
```

## Data Models

### PreProject Model

**Existing Fields** (no schema changes needed):

- `id` - Primary key
- `name` - Project name
- `project_scope` - Required field
- `project_category_id` - Required field (FK to project_categories)
- `implementation_period` - Required field
- `division_id` - Required field (FK to divisions)
- `district_id` - Required field (FK to districts)
- `land_title_status_id` - Required field (FK to land_title_statuses)
- `implementing_agency_id` - Required field (FK to agency_categories)
- `implementation_method_id` - Required field (FK to implementation_methods)
- `project_ownership_id` - Required field (FK to project_ownerships)
- `status` - Current status (Waiting for Approval, Waiting for EPU Approval, Submitted to EPU, etc.)
- `submitted_to_epu_at` - Timestamp when submitted to EPU (nullable)
- `submitted_to_epu_by` - User ID who submitted to EPU (nullable)

**Note**: The fields `submitted_to_epu_at` and `submitted_to_epu_by` need to be added via migration.

### Required Fields Mapping

| Database Field | Display Name | Type |
|---|---|---|
| project_scope | Project Scope | Text |
| project_category_id | Project Category | Foreign Key |
| implementation_period | Implementation Period | String |
| division_id | Division | Foreign Key |
| district_id | District | Foreign Key |
| land_title_status_id | Land Title Status | Foreign Key |
| implementing_agency_id | Implementing Agency | Foreign Key |
| implementation_method_id | Implementation Method | Foreign Key |
| project_ownership_id | Project Ownership | Foreign Key |

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: Completeness Percentage Calculation

*For any* Pre-Project, the completeness percentage should equal the count of filled required fields divided by the total count of required fields, multiplied by 100 and rounded to the nearest integer.

**Validates: Requirements 1.2, 2.2, 7.2, 7.5, 7.6**

### Property 2: Color Coding Based on Percentage

*For any* Pre-Project, the completeness badge color should be red (#dc3545) when percentage is 0-50%, yellow (#ffc107) when 51-80%, and green (#28a745) when 81-100%.

**Validates: Requirements 1.3, 1.4, 1.5**

### Property 3: Field Filled Definition

*For any* required field in a Pre-Project, the field should be considered filled if and only if it contains a non-null, non-empty value, and for foreign key fields, the value references a valid record in the related table.

**Validates: Requirements 2.3, 2.4**

### Property 4: Missing Fields Array Accuracy

*For any* Pre-Project, the array returned by getMissingRequiredFields() should contain exactly those required fields that are empty or null, with their display names.

**Validates: Requirements 6.3, 7.4**

### Property 5: Submit Button Visibility Based on Status

*For any* Pre-Project, the "Submit to EPU" button should be visible if and only if the status is "Waiting for EPU Approval" and the logged-in user has parliament_id or dun_id.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4**

### Property 6: Button Replacement for Waiting Status

*For any* Pre-Project with status "Waiting for EPU Approval", the Edit and Delete buttons should be hidden and only the Submit to EPU button should be displayed.

**Validates: Requirements 3.5**

### Property 7: Validation Prevents Incomplete Submission

*For any* Pre-Project with incomplete data (completeness < 100%), attempting to submit to EPU should fail validation, display a modal with missing fields, and keep the status unchanged at "Waiting for EPU Approval".

**Validates: Requirements 4.1, 4.2, 4.3**

### Property 8: Complete Submission Changes Status

*For any* Pre-Project with complete data (completeness = 100%), submitting to EPU should change the status to "Submitted to EPU" and record the submission timestamp and user ID.

**Validates: Requirements 4.4, 4.6**

### Property 9: Status Transition from NOC

*For any* Pre-Project created from an approved NOC, the initial status should be "Waiting for EPU Approval".

**Validates: Requirements 5.1**

### Property 10: Edit Authorization Based on Status

*For any* Pre-Project, Member of Parliament users should be able to edit when status is "Waiting for EPU Approval", and should not be able to edit when status is "Submitted to EPU".

**Validates: Requirements 5.2, 5.3**

### Property 11: EPU Approver Authorization

*For any* Pre-Project with status "Submitted to EPU", only users designated as EPU approvers should be able to approve or reject it.

**Validates: Requirements 5.4**

### Property 12: Audit Trail Completeness

*For any* status change on a Pre-Project, the system should record the user ID who made the change and the timestamp when it occurred.

**Validates: Requirements 5.5**

### Property 13: Modal Dismissal Preserves Status

*For any* Pre-Project, when the missing fields modal is displayed and then closed, the Pre-Project status should remain unchanged.

**Validates: Requirements 6.5**

## Error Handling

### Validation Errors

**Incomplete Data Submission**:
- Error: User attempts to submit Pre-Project with missing required fields
- Handling: Display modal with list of missing fields, prevent status change
- User Action: Close modal, complete missing fields, retry submission

**Unauthorized Submission**:
- Error: User without parliament_id or dun_id attempts to submit
- Handling: Return error message "You are not authorized to submit Pre-Projects to EPU"
- User Action: Contact administrator for proper role assignment

**Invalid Status Transition**:
- Error: User attempts to submit Pre-Project not in "Waiting for EPU Approval" status
- Handling: Return error message "This Pre-Project cannot be submitted at this stage"
- User Action: Check Pre-Project status, contact administrator if needed

### Database Errors

**Missing Related Records**:
- Error: Foreign key field references non-existent record
- Handling: Treat field as empty in completeness calculation
- User Action: Select valid option from dropdown

**Concurrent Updates**:
- Error: Pre-Project status changed by another user during submission
- Handling: Reload Pre-Project data, show current status
- User Action: Refresh page and retry if appropriate

### Display Errors

**Missing Completeness Data**:
- Error: Completeness calculation fails
- Handling: Display "N/A" instead of percentage
- User Action: Refresh page, contact administrator if persists

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests for comprehensive coverage:

**Unit Tests** focus on:
- Specific examples of completeness calculations (0%, 50%, 100%)
- Modal display when validation fails
- Button visibility for specific user roles and statuses
- Success message display after submission

**Property-Based Tests** focus on:
- Completeness calculation across all possible field combinations
- Color coding correctness for all percentage values
- Validation behavior for all incomplete Pre-Projects
- Status transitions for all valid state changes

### Property-Based Testing Configuration

**Library**: Use PHPUnit with a property-based testing extension (e.g., Eris for PHP)

**Test Configuration**:
- Minimum 100 iterations per property test
- Each test tagged with feature name and property number
- Tag format: `@group Feature: pre-project-data-completeness, Property {number}: {property_text}`

### Unit Test Examples

**Test Completeness Calculation**:
```php
public function test_completeness_is_zero_when_all_fields_empty()
{
    $preProject = PreProject::factory()->create([
        'project_scope' => null,
        'project_category_id' => null,
        'implementation_period' => null,
        'division_id' => null,
        'district_id' => null,
        'land_title_status_id' => null,
        'implementing_agency_id' => null,
        'implementation_method_id' => null,
        'project_ownership_id' => null,
    ]);
    
    $this->assertEquals(0, $preProject->getCompletenessPercentage());
}

public function test_completeness_is_100_when_all_fields_filled()
{
    $preProject = PreProject::factory()->create([
        'project_scope' => 'Test scope',
        'project_category_id' => 1,
        'implementation_period' => '2024-2025',
        'division_id' => 1,
        'district_id' => 1,
        'land_title_status_id' => 1,
        'implementing_agency_id' => 1,
        'implementation_method_id' => 1,
        'project_ownership_id' => 1,
    ]);
    
    $this->assertEquals(100, $preProject->getCompletenessPercentage());
}
```

**Test Submit Button Visibility**:
```php
public function test_submit_button_visible_for_waiting_status_and_parliament_user()
{
    $user = User::factory()->create(['parliament_category_id' => 1]);
    $preProject = PreProject::factory()->create(['status' => 'Waiting for EPU Approval']);
    
    $this->actingAs($user);
    $response = $this->get(route('pages.pre-project'));
    
    $response->assertSee('Submit to EPU');
}

public function test_submit_button_hidden_for_non_parliament_user()
{
    $user = User::factory()->create(['parliament_category_id' => null]);
    $preProject = PreProject::factory()->create(['status' => 'Waiting for EPU Approval']);
    
    $this->actingAs($user);
    $response = $this->get(route('pages.pre-project'));
    
    $response->assertDontSee('Submit to EPU');
}
```

**Test Validation Modal**:
```php
public function test_validation_shows_modal_with_missing_fields()
{
    $user = User::factory()->create(['parliament_category_id' => 1]);
    $preProject = PreProject::factory()->create([
        'status' => 'Waiting for EPU Approval',
        'project_scope' => null,
        'project_category_id' => null,
    ]);
    
    $this->actingAs($user);
    $response = $this->post(route('pages.pre-project.submit-to-epu', $preProject->id));
    
    $response->assertSessionHas('missing_fields');
    $this->assertContains('Project Scope', session('missing_fields'));
    $this->assertContains('Project Category', session('missing_fields'));
}
```

### Property-Based Test Examples

**Property 1: Completeness Percentage Calculation**:
```php
/**
 * @group Feature: pre-project-data-completeness, Property 1: Completeness percentage calculation
 */
public function test_completeness_percentage_is_accurate_for_any_field_combination()
{
    $this->forAll(
        Generator\associative([
            'project_scope' => Generator\oneOf(Generator\constant(null), Generator\string()),
            'project_category_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementation_period' => Generator\oneOf(Generator\constant(null), Generator\string()),
            'division_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'district_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'land_title_status_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementing_agency_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementation_method_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'project_ownership_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
        ])
    )->then(function ($fields) {
        $preProject = PreProject::factory()->create($fields);
        
        $filledCount = count(array_filter($fields, fn($v) => !is_null($v)));
        $totalCount = count($fields);
        $expectedPercentage = (int) round(($filledCount / $totalCount) * 100);
        
        $this->assertEquals($expectedPercentage, $preProject->getCompletenessPercentage());
    });
}
```

**Property 2: Color Coding Based on Percentage**:
```php
/**
 * @group Feature: pre-project-data-completeness, Property 2: Color coding based on percentage
 */
public function test_badge_color_matches_percentage_range_for_any_percentage()
{
    $this->forAll(
        Generator\choose(0, 100)
    )->then(function ($percentage) {
        // Create Pre-Project with specific completeness
        $totalFields = 9;
        $filledFields = (int) round(($percentage / 100) * $totalFields);
        
        $fields = array_fill(0, $filledFields, 'filled');
        $fields = array_pad($fields, $totalFields, null);
        
        $preProject = PreProject::factory()->create([
            'project_scope' => $fields[0],
            'project_category_id' => $fields[1],
            'implementation_period' => $fields[2],
            'division_id' => $fields[3],
            'district_id' => $fields[4],
            'land_title_status_id' => $fields[5],
            'implementing_agency_id' => $fields[6],
            'implementation_method_id' => $fields[7],
            'project_ownership_id' => $fields[8],
        ]);
        
        $color = $preProject->getCompletenessBadgeColor();
        
        if ($percentage >= 81) {
            $this->assertEquals('#28a745', $color);
        } elseif ($percentage >= 51) {
            $this->assertEquals('#ffc107', $color);
        } else {
            $this->assertEquals('#dc3545', $color);
        }
    });
}
```

**Property 7: Validation Prevents Incomplete Submission**:
```php
/**
 * @group Feature: pre-project-data-completeness, Property 7: Validation prevents incomplete submission
 */
public function test_incomplete_preproject_cannot_be_submitted_for_any_missing_fields()
{
    $this->forAll(
        Generator\associative([
            'project_scope' => Generator\oneOf(Generator\constant(null), Generator\string()),
            'project_category_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementation_period' => Generator\oneOf(Generator\constant(null), Generator\string()),
            'division_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'district_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'land_title_status_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementing_agency_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'implementation_method_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
            'project_ownership_id' => Generator\oneOf(Generator\constant(null), Generator\pos()),
        ])
    )->when(function ($fields) {
        // Only test when at least one field is missing
        return in_array(null, $fields, true);
    })->then(function ($fields) {
        $user = User::factory()->create(['parliament_category_id' => 1]);
        $preProject = PreProject::factory()->create(array_merge($fields, [
            'status' => 'Waiting for EPU Approval'
        ]));
        
        $this->actingAs($user);
        $response = $this->post(route('pages.pre-project.submit-to-epu', $preProject->id));
        
        // Status should remain unchanged
        $preProject->refresh();
        $this->assertEquals('Waiting for EPU Approval', $preProject->status);
        
        // Should have missing fields in session
        $response->assertSessionHas('missing_fields');
    });
}
```

### Integration Tests

**Test Complete Workflow**:
1. Create Pre-Project from NOC (status: "Waiting for EPU Approval")
2. Verify completeness is < 100%
3. Attempt submission → validation fails
4. Complete all required fields
5. Verify completeness is 100%
6. Submit to EPU → status changes to "Submitted to EPU"
7. Verify timestamp and user ID recorded
8. Verify Member of Parliament can no longer edit

### Test Data Requirements

**Master Data**:
- Project Categories (at least 3 active)
- Divisions (at least 3 active)
- Districts (at least 3 active)
- Land Title Statuses (at least 3 active)
- Agency Categories (at least 3 active)
- Implementation Methods (at least 3 active)
- Project Ownerships (at least 3 active)

**User Data**:
- Member of Parliament users (with parliament_category_id)
- EPU approver users
- Regular users (without parliament_category_id)

**Pre-Project Data**:
- Complete Pre-Projects (100% completeness)
- Incomplete Pre-Projects (various completeness levels)
- Pre-Projects in different statuses

## Implementation Notes

### Database Migration

A new migration is required to add submission tracking fields:

```php
Schema::table('pre_projects', function (Blueprint $table) {
    $table->timestamp('submitted_to_epu_at')->nullable()->after('status');
    $table->unsignedBigInteger('submitted_to_epu_by')->nullable()->after('submitted_to_epu_at');
    
    $table->foreign('submitted_to_epu_by')->references('id')->on('users')->onDelete('set null');
});
```

### Performance Considerations

**Completeness Calculation**:
- Calculation is performed in-memory (no database queries)
- O(n) complexity where n = number of required fields (constant: 9)
- Can be cached if needed for large datasets

**List View Performance**:
- Completeness calculated for each Pre-Project in the list
- For 100 Pre-Projects: 100 × 9 field checks = 900 operations
- Acceptable performance, no caching needed initially
- Consider caching if list grows beyond 1000 items

### Security Considerations

**Authorization**:
- Submit to EPU: Only Member of Parliament users
- Edit after submission: Blocked for all users
- Approve/Reject: Only EPU approvers

**Input Validation**:
- All required fields validated before status change
- Foreign key fields validated against existing records
- User ID and timestamp recorded for audit trail

### Accessibility

**Modal**:
- Keyboard accessible (ESC to close)
- Screen reader friendly (proper ARIA labels)
- Focus management (return focus after close)

**Color Coding**:
- Not relying solely on color (percentage text also shown)
- Sufficient contrast ratios for all badge colors
- Color-blind friendly palette

### Browser Compatibility

**JavaScript**:
- ES6+ features used (arrow functions, template literals)
- Compatible with modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation for older browsers

**CSS**:
- Flexbox for modal layout
- Standard CSS properties (no experimental features)
- Cross-browser tested
