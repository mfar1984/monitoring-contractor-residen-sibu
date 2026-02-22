# Design Document: Contractor Analysis Transfer System

## Overview

The Contractor Analysis Transfer System provides a mechanism for Agency users and Residen users to transfer multiple projects from the active projects list to contractor analysis. The system implements strict access control based on user agency assignments, requires mandatory documentation, and automatically updates project statuses throughout the transfer lifecycle.

The system follows the existing design patterns established in the Project Transfer and NOC systems, using the data-table component for list views and consistent form styling across all pages.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     User Interface Layer                     │
├─────────────────────────────────────────────────────────────┤
│  - Contractor Analysis List (data-table component)          │
│  - Transfer Creation Form (multi-select + file upload)      │
│  - Transfer Detail View                                      │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    Controller Layer                          │
├─────────────────────────────────────────────────────────────┤
│  - PageController (route handling)                           │
│  - Access control validation                                 │
│  - Request validation                                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Service Layer                            │
├─────────────────────────────────────────────────────────────┤
│  - ContractorAnalysisTransferService                         │
│    - Transfer creation logic                                 │
│    - File upload handling                                    │
│    - Transfer number generation                              │
│    - Project status updates                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
├─────────────────────────────────────────────────────────────┤
│  - ContractorAnalysisTransfer Model                          │
│  - Project Model (status updates)                            │
│  - User Model (access control)                               │
│  - AgencyCategory Model (filtering)                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Database Layer                          │
├─────────────────────────────────────────────────────────────┤
│  - contractor_analysis_transfers table                       │
│  - contractor_analysis_project pivot table                   │
│  - projects table (status field)                             │
│  - users table (access control)                              │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **Transfer Creation Flow**:
   - User navigates to `/pages/contractor-analysis/create`
   - System filters available projects based on user agency
   - User selects multiple projects via checkboxes
   - User uploads mandatory attachment
   - System validates selection and file
   - Service creates transfer record with unique number
   - Service creates pivot table entries for selected projects
   - Service stores attachment file
   - Service updates project statuses to "Analysis Pending"
   - System redirects to list page with success message

2. **Transfer Viewing Flow**:
   - User navigates to `/pages/contractor-analysis`
   - System filters transfers based on user agency
   - System displays transfers using data-table component
   - User clicks "View" action
   - System displays transfer details with projects and attachment

3. **Transfer Deletion Flow**:
   - User clicks "Delete" on draft transfer
   - System prompts for confirmation
   - Service deletes pivot table entries
   - Service deletes attachment file from storage
   - Service rollbacks project statuses to previous state
   - Service deletes transfer record
   - System redirects to list page with success message

## Components and Interfaces

### 1. Database Schema

#### contractor_analysis_transfers Table

```sql
CREATE TABLE contractor_analysis_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_number VARCHAR(50) UNIQUE NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    agency_category_id BIGINT UNSIGNED NOT NULL,
    attachment_path VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (agency_category_id) REFERENCES agency_categories(id) ON DELETE RESTRICT,
    INDEX idx_transfer_number (transfer_number),
    INDEX idx_agency_category (agency_category_id),
    INDEX idx_status (status)
);
```

#### contractor_analysis_project Pivot Table

```sql
CREATE TABLE contractor_analysis_project (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contractor_analysis_transfer_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (contractor_analysis_transfer_id) 
        REFERENCES contractor_analysis_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) 
        REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_transfer_project (contractor_analysis_transfer_id, project_id),
    UNIQUE KEY unique_transfer_project (contractor_analysis_transfer_id, project_id)
);
```

#### Status Master Entry

```sql
-- Add to status_masters table if not exists
INSERT INTO status_masters (name, code, description, status) 
VALUES ('Analysis Pending', 'ANALYSIS_PENDING', 'Project is awaiting contractor analysis', 'Active');
```

### 2. Model Definitions

#### ContractorAnalysisTransfer Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContractorAnalysisTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'created_by',
        'agency_category_id',
        'attachment_path',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(AgencyCategory::class, 'agency_category_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'contractor_analysis_project')
            ->withTimestamps();
    }

    // Helper Methods
    public static function generateTransferNumber(): string
    {
        $year = date('Y');
        $prefix = "CA/{$year}/";
        
        $lastTransfer = self::where('transfer_number', 'LIKE', $prefix . '%')
            ->orderBy('transfer_number', 'desc')
            ->first();
        
        if (!$lastTransfer) {
            return $prefix . '001';
        }
        
        $lastNumber = (int) substr($lastTransfer->transfer_number, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public static function getAvailableProjects(User $user)
    {
        $query = Project::whereNotIn('status', ['Analysis Pending', 'Cancelled']);
        
        // Apply agency filtering for Agency users
        if ($user->agency_category_id) {
            $query->where('agency_category_id', $user->agency_category_id);
        }
        // Residen users see all available projects (no filter)
        
        return $query->with(['agency', 'parliament', 'dun'])
            ->orderBy('project_number')
            ->get();
    }
}
```

### 3. Service Layer

#### ContractorAnalysisTransferService

```php
namespace App\Services;

use App\Models\ContractorAnalysisTransfer;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ContractorAnalysisTransferService
{
    public function createTransfer(array $data, UploadedFile $file, User $user): ContractorAnalysisTransfer
    {
        DB::beginTransaction();
        
        try {
            // Store attachment file
            $attachmentPath = $this->storeAttachment($file);
            
            // Create transfer record
            $transfer = ContractorAnalysisTransfer::create([
                'transfer_number' => ContractorAnalysisTransfer::generateTransferNumber(),
                'created_by' => $user->id,
                'agency_category_id' => $user->agency_category_id ?? $data['agency_category_id'],
                'attachment_path' => $attachmentPath,
                'status' => 'Draft',
            ]);
            
            // Attach projects to transfer
            $transfer->projects()->attach($data['project_ids']);
            
            // Update project statuses
            Project::whereIn('id', $data['project_ids'])
                ->update(['status' => 'Analysis Pending']);
            
            DB::commit();
            
            return $transfer;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if transaction fails
            if (isset($attachmentPath)) {
                Storage::delete($attachmentPath);
            }
            
            throw $e;
        }
    }

    public function deleteTransfer(ContractorAnalysisTransfer $transfer): bool
    {
        if ($transfer->status !== 'Draft') {
            throw new \Exception('Only draft transfers can be deleted');
        }
        
        DB::beginTransaction();
        
        try {
            // Get project IDs before deletion
            $projectIds = $transfer->projects()->pluck('project_id')->toArray();
            
            // Rollback project statuses (assuming previous status was 'Active')
            Project::whereIn('id', $projectIds)
                ->update(['status' => 'Active']);
            
            // Delete attachment file
            if ($transfer->attachment_path) {
                Storage::delete($transfer->attachment_path);
            }
            
            // Delete transfer (cascade will delete pivot entries)
            $transfer->delete();
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function storeAttachment(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('contractor-analysis-attachments', $filename, 'private');
    }

    public function downloadAttachment(ContractorAnalysisTransfer $transfer, User $user)
    {
        // Verify user has permission to access this transfer
        if (!$user->residen_category_id && $user->agency_category_id !== $transfer->agency_category_id) {
            throw new \Exception('Unauthorized access to attachment');
        }
        
        return Storage::download($transfer->attachment_path);
    }
}
```

### 4. Controller Methods

#### PageController Extensions

```php
// List page
public function contractorAnalysis()
{
    $user = auth()->user();
    
    $transfersQuery = ContractorAnalysisTransfer::with(['creator', 'agency', 'projects']);
    
    // Apply access control filtering
    if ($user->agency_category_id) {
        $transfersQuery->where('agency_category_id', $user->agency_category_id);
    }
    // Residen users see all transfers (no filter)
    
    $transfers = $transfersQuery->orderBy('created_at', 'desc')->get();
    
    return view('pages.contractor-analysis', compact('transfers'));
}

// Create form
public function contractorAnalysisCreate()
{
    $user = auth()->user();
    
    // Check authorization
    if (!$user->agency_category_id && !$user->residen_category_id) {
        abort(403, 'Unauthorized. Only Agency and Residen users can create transfers.');
    }
    
    $availableProjects = ContractorAnalysisTransfer::getAvailableProjects($user);
    $agencies = AgencyCategory::where('status', 'Active')->get();
    
    return view('pages.contractor-analysis-create', compact('availableProjects', 'agencies'));
}

// Store transfer
public function contractorAnalysisStore(StoreContractorAnalysisTransferRequest $request)
{
    $user = auth()->user();
    $service = new ContractorAnalysisTransferService();
    
    try {
        $transfer = $service->createTransfer(
            $request->validated(),
            $request->file('attachment'),
            $user
        );
        
        return redirect()
            ->route('pages.contractor-analysis')
            ->with('success', 'Transfer created successfully: ' . $transfer->transfer_number);
            
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to create transfer: ' . $e->getMessage());
    }
}

// Show transfer details
public function contractorAnalysisShow($id)
{
    $user = auth()->user();
    
    $transfer = ContractorAnalysisTransfer::with(['creator', 'agency', 'projects'])
        ->findOrFail($id);
    
    // Verify access
    if (!$user->residen_category_id && $user->agency_category_id !== $transfer->agency_category_id) {
        abort(403, 'Unauthorized access to this transfer');
    }
    
    return view('pages.contractor-analysis-show', compact('transfer'));
}

// Delete transfer
public function contractorAnalysisDelete($id)
{
    $user = auth()->user();
    $service = new ContractorAnalysisTransferService();
    
    $transfer = ContractorAnalysisTransfer::findOrFail($id);
    
    // Verify access
    if (!$user->residen_category_id && $user->agency_category_id !== $transfer->agency_category_id) {
        abort(403, 'Unauthorized to delete this transfer');
    }
    
    try {
        $service->deleteTransfer($transfer);
        
        return redirect()
            ->route('pages.contractor-analysis')
            ->with('success', 'Transfer deleted successfully');
            
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to delete transfer: ' . $e->getMessage());
    }
}

// Download attachment
public function contractorAnalysisDownload($id)
{
    $user = auth()->user();
    $service = new ContractorAnalysisTransferService();
    
    $transfer = ContractorAnalysisTransfer::findOrFail($id);
    
    try {
        return $service->downloadAttachment($transfer, $user);
    } catch (\Exception $e) {
        abort(403, $e->getMessage());
    }
}
```

### 5. Form Request Validation

#### StoreContractorAnalysisTransferRequest

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractorAnalysisTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->agency_category_id || $user->residen_category_id;
    }

    public function rules(): array
    {
        return [
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'required|exists:projects,id',
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'project_ids.required' => 'At least one project must be selected',
            'project_ids.min' => 'At least one project must be selected',
            'project_ids.*.exists' => 'One or more selected projects are invalid',
            'attachment.required' => 'Application letter is required',
            'attachment.mimes' => 'Attachment must be a PDF, DOC, or DOCX file',
            'attachment.max' => 'Attachment size must not exceed 5MB',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            
            // For Agency users, verify all selected projects belong to their agency
            if ($user->agency_category_id) {
                $projectIds = $this->input('project_ids', []);
                $invalidProjects = Project::whereIn('id', $projectIds)
                    ->where('agency_category_id', '!=', $user->agency_category_id)
                    ->count();
                
                if ($invalidProjects > 0) {
                    $validator->errors()->add(
                        'project_ids',
                        'You can only select projects from your agency'
                    );
                }
            }
        });
    }
}
```

## Data Models

### Entity Relationship Diagram

```
┌─────────────────────────────────┐
│ contractor_analysis_transfers   │
├─────────────────────────────────┤
│ id (PK)                         │
│ transfer_number (UNIQUE)        │
│ created_by (FK → users)         │
│ agency_category_id (FK)         │
│ attachment_path                 │
│ status                          │
│ created_at                      │
│ updated_at                      │
└─────────────────────────────────┘
         │                    │
         │ 1                  │ 1
         │                    │
         │ N                  │ N
         ↓                    ↓
┌──────────────────────┐   ┌──────────────────────┐
│ contractor_analysis_ │   │ projects             │
│ project (pivot)      │   │                      │
├──────────────────────┤   ├──────────────────────┤
│ id (PK)              │   │ id (PK)              │
│ contractor_analysis_ │   │ project_number       │
│   transfer_id (FK)   │   │ project_name         │
│ project_id (FK)      │   │ total_cost           │
│ created_at           │   │ status               │
│ updated_at           │   │ agency_category_id   │
└──────────────────────┘   │ parliament_id        │
                           │ dun_basic_id         │
                           └──────────────────────┘
```

### Key Relationships

1. **ContractorAnalysisTransfer → User (creator)**
   - Type: BelongsTo
   - Foreign Key: `created_by`
   - Purpose: Track who created the transfer

2. **ContractorAnalysisTransfer → AgencyCategory**
   - Type: BelongsTo
   - Foreign Key: `agency_category_id`
   - Purpose: Link transfer to agency for filtering

3. **ContractorAnalysisTransfer → Project**
   - Type: BelongsToMany
   - Pivot Table: `contractor_analysis_project`
   - Purpose: Many-to-many relationship for multiple projects per transfer

4. **Project → ContractorAnalysisTransfer**
   - Type: BelongsToMany (inverse)
   - Purpose: Track which transfers include a project


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified the following redundancies and consolidations:

**Redundancy Analysis:**
- Properties 1.1, 1.2, 1.3, 1.4, 1.5 can be consolidated into a single property about button visibility based on user type
- Properties 8.1 and 8.2 can be combined into one property about delete button visibility
- Properties 12.1, 12.2, 12.3, 12.4 all relate to transfer number generation and can be consolidated
- Properties 2.4, 6.3, 7.2, 7.5 all test field display and can be consolidated into properties about complete data rendering
- Properties 4.3 and 4.4 both test file validation and can be combined
- Properties 9.4 and 9.5 test authorization and can be combined

**Consolidated Properties:**
After reflection, the following properties provide unique validation value without redundancy:

### Property 1: Agency-Based Project Filtering

*For any* Agency user and any set of projects, when the user accesses the transfer creation form, all displayed projects should have an `agency_category_id` that matches the user's `agency_category_id`, and no projects with "Analysis Pending" status should be displayed.

**Validates: Requirements 2.1, 2.3**

### Property 2: Residen User Full Access

*For any* Residen user, when accessing the transfer creation form or transfer list page, all available projects (excluding "Analysis Pending") should be displayed regardless of agency, and all transfers should be visible regardless of agency.

**Validates: Requirements 2.2, 6.2**

### Property 3: Required Fields Display

*For any* project in the available projects list, the rendered output should contain the project number, project name, total cost, and current status fields.

**Validates: Requirements 2.4**

### Property 4: Multiple Project Selection Validation

*For any* transfer submission with an empty project selection array, the validation should fail and return an error message indicating at least one project must be selected.

**Validates: Requirements 3.3**

### Property 5: File Upload Validation

*For any* file upload, if the file format is not PDF, DOC, or DOCX, or if the file size exceeds 5MB, the validation should fail and return a descriptive error message indicating the specific validation failure.

**Validates: Requirements 4.2, 4.3, 4.4**

### Property 6: Transfer Number Format and Uniqueness

*For any* newly created transfer, the generated transfer number should follow the format CA/YYYY/### where YYYY is the current year and ### is a sequential number, and the transfer number should be unique across all existing transfers.

**Validates: Requirements 5.1, 12.1, 12.2, 12.3, 12.4**

### Property 7: Project Status Update on Transfer

*For any* set of projects included in a successfully submitted transfer, all projects should have their status changed to "Analysis Pending" immediately after submission.

**Validates: Requirements 5.3**

### Property 8: Transfer Metadata Recording

*For any* successfully created transfer, the transfer record should contain the `created_by` field matching the submitting user's ID and the `agency_category_id` field matching the user's agency (or the agency of the projects for Residen users).

**Validates: Requirements 5.4**

### Property 9: Agency-Based Transfer List Filtering

*For any* Agency user accessing the transfer list page, all displayed transfers should have an `agency_category_id` that matches the user's `agency_category_id`.

**Validates: Requirements 6.1**

### Property 10: Transfer List Complete Data Display

*For any* transfer in the transfer list, the rendered output should contain the transfer number, agency name, projects count, status, created date, and action buttons.

**Validates: Requirements 6.3**

### Property 11: Transfer Search Functionality

*For any* search query on the transfer list page, if the query matches a transfer number (partial or complete), that transfer should appear in the search results.

**Validates: Requirements 6.5**

### Property 12: Transfer Detail Complete Data Display

*For any* transfer detail page, the rendered output should contain the transfer number, agency name, created date, status, creator information, all associated projects with their details (project number, name, total cost, status), and a download link for the attachment.

**Validates: Requirements 7.2, 7.3, 7.5**

### Property 13: Delete Button Conditional Display

*For any* transfer, the delete action button should be visible if and only if the transfer status is "Draft".

**Validates: Requirements 8.1, 8.2**

### Property 14: Transfer Deletion Cleanup

*For any* draft transfer that is deleted, the system should remove the transfer record, all pivot table entries, the attachment file from storage, and rollback all associated project statuses to "Active".

**Validates: Requirements 8.4, 8.5, 8.6**

### Property 15: File Storage and Path Recording

*For any* uploaded attachment file, the file should be stored in the "contractor-analysis-attachments" directory with a unique filename, and the file path should be recorded in the transfer's `attachment_path` field.

**Validates: Requirements 9.1, 9.2, 9.3**

### Property 16: Attachment Download Authorization

*For any* attachment download request, if the requesting user is not a Residen user and their `agency_category_id` does not match the transfer's `agency_category_id`, the download should be denied with an authorization error.

**Validates: Requirements 9.4, 9.5**

### Property 17: Validation Error Messages

*For any* form submission that fails validation, the system should return specific error messages indicating which fields failed validation and why (e.g., "At least one project must be selected", "Attachment must be PDF, DOC, or DOCX", "File size must not exceed 5MB").

**Validates: Requirements 10.2, 10.3, 10.4**

### Property 18: Agency Project Selection Validation

*For any* Agency user submitting a transfer, if any selected project has an `agency_category_id` that does not match the user's `agency_category_id`, the validation should fail with an error message "You can only select projects from your agency".

**Validates: Requirements 10.4**

### Property 19: Sequential Number Increment

*For any* two transfers created in sequence within the same year, the sequential number portion of the second transfer's transfer number should be exactly one greater than the first transfer's sequential number.

**Validates: Requirements 12.3**

## Error Handling

### Validation Errors

**Form Validation Failures:**
- Empty project selection → Return error: "At least one project must be selected"
- Missing attachment → Return error: "Application letter is required"
- Invalid file format → Return error: "Attachment must be a PDF, DOC, or DOCX file"
- File too large → Return error: "Attachment size must not exceed 5MB"
- Agency mismatch → Return error: "You can only select projects from your agency"

**Error Response Format:**
```php
return back()
    ->withInput()
    ->withErrors(['field_name' => 'Error message']);
```

### Authorization Errors

**Unauthorized Access:**
- Non-Agency/Non-Residen user attempts to create transfer → HTTP 403 with message: "Unauthorized. Only Agency and Residen users can create transfers."
- User attempts to view transfer from different agency → HTTP 403 with message: "Unauthorized access to this transfer"
- User attempts to download attachment without permission → HTTP 403 with message: "Unauthorized access to attachment"

**Error Response Format:**
```php
abort(403, 'Error message');
```

### Database Transaction Errors

**Transaction Rollback Scenarios:**
- File upload fails after transfer creation → Rollback transfer creation, delete uploaded file
- Project status update fails → Rollback entire transaction including transfer creation and file upload
- Pivot table insertion fails → Rollback entire transaction

**Error Handling Pattern:**
```php
DB::beginTransaction();
try {
    // Operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Cleanup (delete uploaded files, etc.)
    throw $e;
}
```

### File Storage Errors

**File Upload Failures:**
- Disk full → Return error: "Failed to upload file: insufficient storage space"
- Permission denied → Return error: "Failed to upload file: permission denied"
- Invalid file → Return error: "Failed to upload file: file is corrupted or invalid"

**File Deletion Failures:**
- File not found during deletion → Log warning but continue (file may have been manually deleted)
- Permission denied → Log error and notify administrator

### Status Update Errors

**Project Status Update Failures:**
- Project not found → Rollback transaction, return error: "One or more projects no longer exist"
- Project already in Analysis Pending → Skip update (idempotent operation)
- Database constraint violation → Rollback transaction, return error: "Failed to update project status"

## Testing Strategy

### Dual Testing Approach

The Contractor Analysis Transfer System requires both unit testing and property-based testing for comprehensive coverage:

**Unit Tests:**
- Specific examples of transfer creation with valid data
- Edge cases: empty project selection, missing attachment, invalid file formats
- Error conditions: unauthorized access, agency mismatch, file size exceeded
- Integration points: file storage, database transactions, status updates

**Property-Based Tests:**
- Universal properties across all inputs (see Correctness Properties section)
- Comprehensive input coverage through randomization
- Minimum 100 iterations per property test

### Property-Based Testing Configuration

**Testing Library:** For Laravel/PHP, use **Pest with Pest Property Plugin** or **PHPUnit with Eris**

**Test Configuration:**
- Minimum 100 iterations per property test
- Each test must reference its design document property
- Tag format: **Feature: contractor-analysis-transfer, Property {number}: {property_text}**

**Example Property Test Structure:**
```php
use function Pest\property;

it('filters projects by agency for Agency users', function () {
    property()
        ->iterations(100)
        ->forAll(
            agencyUser(),
            projectSet()
        )
        ->then(function ($user, $projects) {
            $availableProjects = ContractorAnalysisTransfer::getAvailableProjects($user);
            
            foreach ($availableProjects as $project) {
                expect($project->agency_category_id)->toBe($user->agency_category_id);
                expect($project->status)->not->toBe('Analysis Pending');
            }
        })
        ->tag('Feature: contractor-analysis-transfer, Property 1: Agency-Based Project Filtering');
})->group('property-based');
```

### Unit Test Examples

**Transfer Creation Test:**
```php
test('Agency user can create transfer with valid data', function () {
    $user = User::factory()->create(['agency_category_id' => 1]);
    $projects = Project::factory()->count(3)->create(['agency_category_id' => 1]);
    $file = UploadedFile::fake()->create('letter.pdf', 1024);
    
    $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
        'project_ids' => $projects->pluck('id')->toArray(),
        'attachment' => $file,
    ]);
    
    $response->assertRedirect(route('pages.contractor-analysis'));
    $this->assertDatabaseHas('contractor_analysis_transfers', [
        'created_by' => $user->id,
        'agency_category_id' => 1,
    ]);
    
    foreach ($projects as $project) {
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'Analysis Pending',
        ]);
    }
});
```

**Validation Test:**
```php
test('transfer creation fails without attachment', function () {
    $user = User::factory()->create(['agency_category_id' => 1]);
    $projects = Project::factory()->count(2)->create(['agency_category_id' => 1]);
    
    $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
        'project_ids' => $projects->pluck('id')->toArray(),
        // No attachment
    ]);
    
    $response->assertSessionHasErrors(['attachment']);
});
```

**Authorization Test:**
```php
test('Agency user cannot select projects from different agency', function () {
    $user = User::factory()->create(['agency_category_id' => 1]);
    $projects = Project::factory()->count(2)->create(['agency_category_id' => 2]); // Different agency
    $file = UploadedFile::fake()->create('letter.pdf', 1024);
    
    $response = $this->actingAs($user)->post('/pages/contractor-analysis', [
        'project_ids' => $projects->pluck('id')->toArray(),
        'attachment' => $file,
    ]);
    
    $response->assertSessionHasErrors(['project_ids']);
});
```

### Testing Coverage Goals

- **Unit Tests:** 80%+ code coverage
- **Property Tests:** All 19 correctness properties implemented
- **Integration Tests:** All user workflows (create, view, delete)
- **Edge Cases:** File size limits, number overflow, concurrent transfers
- **Error Conditions:** All validation failures, authorization failures, transaction rollbacks
