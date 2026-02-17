# Design Document: NOC System Restructuring

## Overview

This design restructures the NOC (Notice of Change) system from its incorrect location under Pre-Project to its proper location under Project. The restructuring involves creating a Projects table, implementing a project transfer mechanism, migrating all NOC functionality to reference projects instead of pre-projects, and creating a three-tab Project page interface.

The key architectural change is introducing a clear separation between:
- **Pre-Projects**: Proposals awaiting approval
- **Projects**: Approved pre-projects with assigned project numbers
- **NOCs**: Change requests for approved projects

This aligns the system with the correct business workflow and ensures NOCs only operate on approved projects.

## Architecture

### High-Level Architecture

```
┌─────────────────┐
│  Pre-Project    │
│   (Proposals)   │
└────────┬────────┘
         │ Approval
         ▼
┌─────────────────┐      ┌─────────────────┐
│    Projects     │◄─────┤  Project        │
│   (Approved)    │      │  Transfer       │
└────────┬────────┘      │  Service        │
         │               └─────────────────┘
         │
         ▼
┌─────────────────┐
│   NOC System    │
│ (Change Mgmt)   │
└─────────────────┘
```

### Data Flow

1. **Pre-Project Creation**: User creates pre-project proposal
2. **Approval**: Pre-project goes through approval workflow
3. **Transfer**: Approved pre-project automatically transfers to Projects table
4. **Project Number Assignment**: System generates unique project number
5. **NOC Creation**: Users can create NOCs for approved projects
6. **NOC Workflow**: NOC goes through two-level approval process
7. **Status Sync**: Project status updates based on NOC status

### Module Structure

```
app/
├── Models/
│   ├── PreProject.php (existing)
│   ├── Project.php (new)
│   └── Noc.php (updated)
├── Services/
│   └── ProjectTransferService.php (new)
└── Http/Controllers/Pages/
    └── PageController.php (updated)

database/
└── migrations/
    ├── create_projects_table.php (new)
    ├── rename_noc_pre_project_to_noc_project.php (new)
    └── migrate_noc_data_to_projects.php (new)

resources/views/pages/
├── project.blade.php (new - main page with tabs)
├── project-noc.blade.php (renamed from pre-project-noc)
├── project-noc-create.blade.php (renamed)
├── project-noc-show.blade.php (renamed)
└── project-noc-print.blade.php (renamed)
```

## Components and Interfaces

### 1. Project Model

**Purpose**: Represents approved pre-projects that have been transferred to the projects system.

**File**: `app/Models/Project.php`

**Properties**:
```php
protected $fillable = [
    // Project identification
    'project_number',        // Unique: PROJ/YYYY/###
    'pre_project_id',        // FK to pre_projects
    'approval_date',         // When pre-project was approved
    'transferred_at',        // When transfer occurred
    
    // All fields from PreProject model
    'name',
    'residen_category_id',
    'agency_category_id',
    'parliament_id',
    'dun_basic_id',
    'project_category_id',
    'project_scope',
    'actual_project_cost',
    'consultation_cost',
    'lss_inspection_cost',
    'sst',
    'others_cost',
    'total_cost',
    'implementation_period',
    'division_id',
    'district_id',
    'parliament_location_id',
    'dun_id',
    'site_layout',
    'land_title_status_id',
    'consultation_service',
    'implementing_agency_id',
    'implementation_method_id',
    'project_ownership_id',
    'jkkk_name',
    'state_government_asset',
    'bill_of_quantity',
    'bill_of_quantity_attachment',
    'status',  // Active, NOC, Cancelled
];
```

**Relationships**:
```php
// Belongs to original pre-project
public function preProject(): BelongsTo

// Has many NOCs
public function nocs(): BelongsToMany

// All master data relationships (same as PreProject)
public function residenCategory(): BelongsTo
public function agencyCategory(): BelongsTo
public function parliament(): BelongsTo
// ... (all other relationships from PreProject)
```

**Methods**:
```php
// Generate unique project number
public static function generateProjectNumber(): string

// Scope: Filter by parliament
public function scopeForParliament($query, $parliamentId)

// Scope: Filter by DUN
public function scopeForDun($query, $dunId)

// Scope: Filter by user access
public function scopeForUser($query, User $user)
```

### 2. ProjectTransferService

**Purpose**: Handles the transfer of approved pre-projects to the projects table.

**File**: `app/Services/ProjectTransferService.php`

**Interface**:
```php
class ProjectTransferService
{
    /**
     * Transfer an approved pre-project to projects table
     * 
     * @param PreProject $preProject
     * @return Project
     * @throws \Exception if pre-project not approved
     */
    public function transfer(PreProject $preProject): Project
    
    /**
     * Check if pre-project can be transferred
     * 
     * @param PreProject $preProject
     * @return bool
     */
    public function canTransfer(PreProject $preProject): bool
    
    /**
     * Get project for a pre-project (if already transferred)
     * 
     * @param PreProject $preProject
     * @return Project|null
     */
    public function getProjectForPreProject(PreProject $preProject): ?Project
}
```

**Implementation Logic**:
```php
public function transfer(PreProject $preProject): Project
{
    // 1. Validate pre-project is approved
    if ($preProject->status !== 'Approved') {
        throw new \Exception('Only approved pre-projects can be transferred');
    }
    
    // 2. Check if already transferred
    $existing = Project::where('pre_project_id', $preProject->id)->first();
    if ($existing) {
        return $existing;
    }
    
    // 3. Generate project number
    $projectNumber = Project::generateProjectNumber();
    
    // 4. Copy all data from pre-project
    $projectData = $preProject->toArray();
    unset($projectData['id'], $projectData['created_at'], $projectData['updated_at']);
    
    // 5. Add project-specific fields
    $projectData['project_number'] = $projectNumber;
    $projectData['pre_project_id'] = $preProject->id;
    $projectData['approval_date'] = now();
    $projectData['transferred_at'] = now();
    $projectData['status'] = 'Active';
    
    // 6. Create project record
    return Project::create($projectData);
}
```

### 3. Updated Noc Model

**Purpose**: Manages NOC records with relationships to projects instead of pre-projects.

**File**: `app/Models/Noc.php` (updated)

**Updated Relationships**:
```php
/**
 * Get all projects in this NOC
 * CHANGED: from preProjects() to projects()
 */
public function projects()
{
    return $this->belongsToMany(Project::class, 'noc_project')
        ->withPivot([
            'tahun_rtp',
            'no_projek',
            'nama_projek_asal',
            'nama_projek_baru',
            'kos_asal',
            'kos_baru',
            'agensi_pelaksana_asal',
            'agensi_pelaksana_baru',
            'noc_note_id'
        ])
        ->withTimestamps();
}

// REMOVED: preProjects() relationship
```

**Updated Methods**:
```php
/**
 * Get available projects for NOC import
 * CHANGED: Query projects table instead of pre_projects
 */
public static function getAvailableProjects(User $user)
{
    $query = Project::query();
    
    // Filter by user's parliament or DUN
    if ($user->parliament_category_id) {
        $query->where('parliament_id', $user->parliament_category_id);
    } elseif ($user->dun_id) {
        $query->where('dun_id', $user->dun_id);
    }
    
    // Exclude projects already in NOCs
    $query->whereDoesntHave('nocs');
    
    // Only active projects
    $query->where('status', 'Active');
    
    return $query->get();
}
```

### 4. Project Page Component

**Purpose**: Main project page with three tabs for Project, NOC, and Project Cancel.

**File**: `resources/views/pages/project.blade.php`

**Structure**:
```blade
<x-layout>
    <!-- Breadcrumb -->
    <div class="breadcrumb">Home > Project</div>
    
    <!-- Tabs Component -->
    <x-project-tabs active="project" />
    
    <!-- Tab Content -->
    <div class="tab-content">
        @if($activeTab === 'project')
            <!-- Project List using data-table component -->
            <x-data-table
                title="Projects"
                description="List of approved projects transferred from Pre-Project."
                createButtonText=""
                createButtonRoute=""
                searchPlaceholder="Search projects..."
                :columns="['Project Number', 'Project Name', 'Parliament/DUN', 'Total Cost', 'Approval Date', 'Status', 'Actions']"
                :data="$projects"
                :rowsPerPage="10"
            >
                <!-- Project rows -->
            </x-data-table>
        @elseif($activeTab === 'noc')
            <!-- NOC List (redirect to /pages/project/noc) -->
        @elseif($activeTab === 'cancel')
            <!-- Placeholder for Project Cancel -->
            <div class="placeholder">
                Project Cancel functionality coming soon.
            </div>
        @endif
    </div>
</x-layout>
```

### 5. Project Tabs Component

**Purpose**: Reusable tab navigation for Project page.

**File**: `resources/views/components/project-tabs.blade.php`

**Interface**:
```blade
@props(['active' => 'project'])

<div class="tabs-container">
    <div class="tabs-scroll">
        <a href="{{ route('pages.project') }}" 
           class="tab {{ $active === 'project' ? 'active' : '' }}">
            Project
        </a>
        <a href="{{ route('pages.project.noc') }}" 
           class="tab {{ $active === 'noc' ? 'active' : '' }}">
            NOC
        </a>
        <a href="{{ route('pages.project.cancel') }}" 
           class="tab {{ $active === 'cancel' ? 'active' : '' }}">
            Project Cancel
        </a>
    </div>
</div>
```

**Styling**: Uses same CSS as `master-data-tabs` component with horizontal drag scrolling.

### 6. Updated NOC Controllers

**Purpose**: Handle NOC operations with projects instead of pre-projects.

**File**: `app/Http/Controllers/Pages/PageController.php`

**Updated Methods**:

```php
// RENAMED: preProjectNoc() → projectNoc()
public function projectNoc()
{
    $user = Auth::user();
    
    // Filter NOCs by user's parliament or DUN
    $query = Noc::query();
    if ($user->parliament_category_id) {
        $query->where('parliament_id', $user->parliament_category_id);
    } elseif ($user->dun_id) {
        $query->where('dun_id', $user->dun_id);
    }
    
    $nocs = $query->with(['projects', 'parliament', 'dun'])->get();
    
    return view('pages.project-noc', compact('nocs'));
}

// RENAMED: preProjectNocCreate() → projectNocCreate()
public function projectNocCreate()
{
    $user = Auth::user();
    
    // Get available projects (CHANGED from pre-projects)
    $availableProjects = Noc::getAvailableProjects($user);
    
    // Get master data
    $agencies = AgencyCategory::where('status', 'Active')->get();
    $nocNotes = NocNote::where('status', 'Active')->get();
    
    return view('pages.project-noc-create', compact('availableProjects', 'agencies', 'nocNotes'));
}

// RENAMED: preProjectNocStore() → projectNocStore()
public function projectNocStore(Request $request)
{
    // Validation
    $validated = $request->validate([
        'noc_date' => 'required|date',
        'projects' => 'required|array|min:1',
        'projects.*.project_id' => 'required|exists:projects,id',
        'projects.*.tahun_rtp' => 'required|string',
        'projects.*.no_projek' => 'required|string',
        // ... other validations
    ]);
    
    // Create NOC
    $noc = Noc::create([
        'noc_number' => Noc::generateNocNumber(),
        'parliament_id' => Auth::user()->parliament_category_id,
        'dun_id' => Auth::user()->dun_id,
        'noc_date' => $validated['noc_date'],
        'created_by' => Auth::id(),
        'status' => 'Draft',
    ]);
    
    // Attach projects (CHANGED from pre-projects)
    foreach ($validated['projects'] as $projectData) {
        $noc->projects()->attach($projectData['project_id'], [
            'tahun_rtp' => $projectData['tahun_rtp'],
            'no_projek' => $projectData['no_projek'],
            // ... other pivot data
        ]);
    }
    
    return redirect()->route('pages.project.noc')->with('success', 'NOC created successfully');
}

// Similar updates for other methods:
// - projectNocShow()
// - projectNocSubmit()
// - projectNocApprove()
// - projectNocReject()
// - projectNocPrint()
// - projectNocDelete()
```

## Data Models

### Projects Table Schema

```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Project identification
    project_number VARCHAR(255) UNIQUE NOT NULL,  -- PROJ/YYYY/###
    pre_project_id BIGINT UNSIGNED NOT NULL,
    approval_date TIMESTAMP NOT NULL,
    transferred_at TIMESTAMP NOT NULL,
    
    -- All fields from pre_projects table
    name VARCHAR(255) NOT NULL,
    residen_category_id BIGINT UNSIGNED NULL,
    agency_category_id BIGINT UNSIGNED NULL,
    parliament_id BIGINT UNSIGNED NULL,
    dun_basic_id BIGINT UNSIGNED NULL,
    project_category_id BIGINT UNSIGNED NULL,
    project_scope TEXT NULL,
    actual_project_cost DECIMAL(15,2) NULL,
    consultation_cost DECIMAL(15,2) NULL,
    lss_inspection_cost DECIMAL(15,2) NULL,
    sst DECIMAL(15,2) NULL,
    others_cost DECIMAL(15,2) NULL,
    total_cost DECIMAL(15,2) NULL,
    implementation_period VARCHAR(255) NULL,
    division_id BIGINT UNSIGNED NULL,
    district_id BIGINT UNSIGNED NULL,
    parliament_location_id BIGINT UNSIGNED NULL,
    dun_id BIGINT UNSIGNED NULL,
    site_layout ENUM('Yes', 'No') NULL,
    land_title_status_id BIGINT UNSIGNED NULL,
    consultation_service ENUM('Yes', 'No') NULL,
    implementing_agency_id BIGINT UNSIGNED NULL,
    implementation_method_id BIGINT UNSIGNED NULL,
    project_ownership_id BIGINT UNSIGNED NULL,
    jkkk_name VARCHAR(255) NULL,
    state_government_asset ENUM('Yes', 'No') NULL,
    bill_of_quantity ENUM('Yes', 'No') NULL,
    bill_of_quantity_attachment VARCHAR(255) NULL,
    status ENUM('Active', 'NOC', 'Cancelled') DEFAULT 'Active',
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (pre_project_id) REFERENCES pre_projects(id) ON DELETE RESTRICT,
    FOREIGN KEY (residen_category_id) REFERENCES residen_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (agency_category_id) REFERENCES agency_categories(id) ON DELETE SET NULL,
    -- ... (all other foreign keys same as pre_projects)
    
    -- Indexes
    INDEX idx_project_number (project_number),
    INDEX idx_pre_project_id (pre_project_id),
    INDEX idx_parliament_id (parliament_id),
    INDEX idx_dun_id (dun_id),
    INDEX idx_status (status)
);
```

### NOC Project Pivot Table Schema

```sql
-- RENAMED from noc_pre_project to noc_project
CREATE TABLE noc_project (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    noc_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,  -- CHANGED from pre_project_id
    
    -- Project change details
    tahun_rtp VARCHAR(255) NULL,
    no_projek VARCHAR(255) NULL,
    nama_projek_asal VARCHAR(255) NULL,
    nama_projek_baru VARCHAR(255) NULL,
    kos_asal DECIMAL(15,2) NULL,
    kos_baru DECIMAL(15,2) NULL,
    agensi_pelaksana_asal VARCHAR(255) NULL,
    agensi_pelaksana_baru VARCHAR(255) NULL,
    noc_note_id BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (noc_id) REFERENCES nocs(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (noc_note_id) REFERENCES noc_notes(id) ON DELETE SET NULL,
    
    -- Unique constraint
    UNIQUE KEY unique_noc_project (noc_id, project_id)
);
```

### Data Migration Strategy

**Phase 1: Create Projects Table**
```php
// Migration: create_projects_table.php
Schema::create('projects', function (Blueprint $table) {
    // ... (schema as defined above)
});
```

**Phase 2: Transfer Approved Pre-Projects**
```php
// Migration: transfer_approved_pre_projects.php
$approvedPreProjects = PreProject::where('status', 'Approved')->get();

foreach ($approvedPreProjects as $preProject) {
    $projectNumber = Project::generateProjectNumber();
    
    $projectData = $preProject->toArray();
    unset($projectData['id'], $projectData['created_at'], $projectData['updated_at']);
    
    Project::create(array_merge($projectData, [
        'project_number' => $projectNumber,
        'pre_project_id' => $preProject->id,
        'approval_date' => $preProject->updated_at,
        'transferred_at' => now(),
        'status' => 'Active',
    ]));
}
```

**Phase 3: Rename Pivot Table**
```php
// Migration: rename_noc_pre_project_to_noc_project.php
Schema::rename('noc_pre_project', 'noc_project');
```

**Phase 4: Update Pivot Table Foreign Key**
```php
// Migration: update_noc_project_foreign_key.php
Schema::table('noc_project', function (Blueprint $table) {
    // Drop old foreign key
    $table->dropForeign(['pre_project_id']);
    
    // Rename column
    $table->renameColumn('pre_project_id', 'project_id');
    
    // Add new foreign key
    $table->foreign('project_id')
          ->references('id')
          ->on('projects')
          ->onDelete('cascade');
});
```

**Phase 5: Map NOC Data to Projects**
```php
// Migration: map_noc_data_to_projects.php
$nocProjects = DB::table('noc_project')->get();

foreach ($nocProjects as $nocProject) {
    // Find the project that corresponds to this pre-project
    $project = Project::where('pre_project_id', $nocProject->project_id)->first();
    
    if ($project) {
        // Update the pivot record to reference the project
        DB::table('noc_project')
            ->where('id', $nocProject->id)
            ->update(['project_id' => $project->id]);
    }
}
```

## Correctness Properties


*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified the following testable properties and performed reflection to eliminate redundancy:

**Redundancy Analysis:**
- Properties 2.3 (copy all data) and 2.4 (set pre_project_id) can be combined into a single comprehensive "transfer completeness" property
- Properties 2.5 and 2.6 (timestamp setting) can be combined into a single "timestamp initialization" property
- Properties 13.1, 13.3, and 13.4 all deal with status synchronization and can be consolidated into a comprehensive "NOC status sync" property
- Properties 14.1 and 14.2 both deal with migration completeness and can be combined

**Final Property Set:**
After reflection, the following properties provide unique validation value without redundancy.

### Property 1: Project Number Uniqueness

*For any* set of project transfers, all generated project numbers should be unique across the entire projects table.

**Validates: Requirements 1.2, 2.2**

### Property 2: Project Number Format Compliance

*For any* generated project number, it should match the pattern "PROJ/YYYY/###" where YYYY is the current year and ### is a zero-padded sequential number.

**Validates: Requirements 2.7**

### Property 3: Project Transfer Completeness

*For any* approved pre-project, when transferred to a project, the resulting project should contain all pre-project data fields and have pre_project_id correctly referencing the original pre-project.

**Validates: Requirements 2.3, 2.4**

### Property 4: Project Transfer Trigger

*For any* pre-project, when its status changes to "Approved", a corresponding project record should be created automatically.

**Validates: Requirements 2.1**

### Property 5: Transfer Timestamp Initialization

*For any* project transfer, both approval_date and transferred_at fields should be set to valid timestamps at the time of transfer.

**Validates: Requirements 2.5, 2.6**

### Property 6: NOC Data Migration Mapping

*For any* existing noc_pre_project record, after migration there should be a corresponding noc_project record with the correct project_id mapping.

**Validates: Requirements 3.7**

### Property 7: Pivot Data Preservation

*For any* NOC-project relationship, all custom pivot fields (tahun_rtp, no_projek, nama_projek_asal, nama_projek_baru, kos_asal, kos_baru, agensi_pelaksana_asal, agensi_pelaksana_baru, noc_note_id) should be accessible through the relationship.

**Validates: Requirements 4.6**

### Property 8: User-Based Project Filtering

*For any* user with parliament_id or dun_id, the project list should only display projects matching their parliament or DUN assignment.

**Validates: Requirements 9.3, 9.7**

### Property 9: Project Search Functionality

*For any* search query, the returned projects should contain the search term in at least one of their searchable fields (project_number, name, project_scope).

**Validates: Requirements 9.4**

### Property 10: Available Projects Exclusion

*For any* user, the NOC import modal should exclude projects that are already included in existing NOCs.

**Validates: Requirements 10.3**

### Property 11: User-Based NOC Import Filtering

*For any* user with parliament_id or dun_id, the NOC import modal should only display projects matching their parliament or DUN assignment.

**Validates: Requirements 10.2**

### Property 12: Active Tab Highlighting

*For any* route under /pages/project/*, the corresponding tab in the project tabs component should be highlighted as active.

**Validates: Requirements 8.7, 11.5**

### Property 13: NOC Status Synchronization

*For any* NOC with attached projects:
- When NOC status changes to "Pending First Approval" or "Pending Second Approval", all attached projects status should change to "NOC"
- When NOC status changes to "Rejected", all attached projects status should rollback to "Active"
- When NOC is deleted (Draft only), all attached projects status should rollback to "Active"

**Validates: Requirements 13.1, 13.2, 13.3, 13.4**

### Property 14: Migration Data Completeness

*For all* approved pre-projects before migration, there should be a corresponding project record after migration, and all noc_pre_project records should be mapped to noc_project records.

**Validates: Requirements 14.1, 14.2**

### Property 15: Migration Data Preservation

*For any* NOC record, after migration all NOC data (noc_number, status, approvals, attachments) should remain unchanged.

**Validates: Requirements 14.3**

### Property 16: Migration Referential Integrity

*For all* records after migration, all foreign key relationships between nocs, projects, and noc_project tables should be valid (no orphaned records).

**Validates: Requirements 14.4, 14.7**

### Property 17: NOC Change Detection

*For any* NOC project with pivot data, the system should correctly identify whether changes exist (new project name, new cost, or new agency).

**Validates: Requirements 17.13**

### Property 18: Pre-Project Creation from NOC Changes

*For any* imported NOC project with changes, when NOC is submitted, a corresponding pre-project record should be created with status "Waiting For EPU Approval".

**Validates: Requirements 17.2, 17.3**

### Property 19: Pre-Project Data Accuracy

*For any* pre-project created from NOC changes:
- If new project name exists, pre-project name should match new name, otherwise original name
- If new cost exists, pre-project cost should match new cost, otherwise original cost
- If new agency exists, pre-project agency should match new agency, otherwise original agency
- Project number should match the original project number

**Validates: Requirements 17.4, 17.5, 17.6, 17.7, 17.8**

### Property 20: New Project Exclusion

*For any* NOC project added via "Add New" button (no Project Number), when NOC is submitted, no pre-project record should be created.

**Validates: Requirements 17.12**

### Property 21: Pre-Project Visibility

*For all* pre-projects created from NOC changes, they should be visible in the Pre-Project list at /pages/pre-project with status "Waiting For EPU Approval" and Project Number populated.

**Validates: Requirements 17.9, 17.10, 17.11**

### 7. NOC to Pre-Project Integration Service

**Purpose**: Handles the creation of new pre-project records when NOC contains imported projects with changes.

**File**: `app/Services/NocToPreProjectService.php`

**Interface**:
```php
class NocToPreProjectService
{
    /**
     * Process NOC submission and create pre-projects for changed imported projects
     * 
     * @param Noc $noc
     * @return array Array of created PreProject records
     */
    public function processNocSubmission(Noc $noc): array
    
    /**
     * Check if a NOC project has changes that require pre-project creation
     * 
     * @param array $nocProjectData Pivot data from noc_project
     * @return bool
     */
    public function hasChanges(array $nocProjectData): bool
    
    /**
     * Create pre-project record from NOC project data
     * 
     * @param Project $originalProject
     * @param array $nocProjectData Pivot data with changes
     * @return PreProject
     */
    public function createPreProjectFromNocChanges(Project $originalProject, array $nocProjectData): PreProject
}
```

**Implementation Logic**:
```php
public function processNocSubmission(Noc $noc): array
{
    $createdPreProjects = [];
    
    // Get all projects in this NOC with pivot data
    $nocProjects = $noc->projects()->get();
    
    foreach ($nocProjects as $project) {
        $pivotData = $project->pivot->toArray();
        
        // Only process imported projects (those with no_projek/Project Number)
        if (empty($pivotData['no_projek'])) {
            continue; // Skip "Add New" projects
        }
        
        // Check if project has changes
        if (!$this->hasChanges($pivotData)) {
            continue; // No changes, skip
        }
        
        // Create new pre-project record
        $preProject = $this->createPreProjectFromNocChanges($project, $pivotData);
        $createdPreProjects[] = $preProject;
    }
    
    return $createdPreProjects;
}

public function hasChanges(array $nocProjectData): bool
{
    // Check if any of the "new" fields have values
    return !empty($nocProjectData['nama_projek_baru']) ||
           !empty($nocProjectData['kos_baru']) ||
           !empty($nocProjectData['agensi_pelaksana_baru']);
}

public function createPreProjectFromNocChanges(Project $originalProject, array $nocProjectData): PreProject
{
    // Start with original project data
    $preProjectData = $originalProject->toArray();
    
    // Remove fields that shouldn't be copied
    unset($preProjectData['id'], $preProjectData['project_number'], 
          $preProjectData['pre_project_id'], $preProjectData['approval_date'], 
          $preProjectData['transferred_at'], $preProjectData['created_at'], 
          $preProjectData['updated_at']);
    
    // Apply changes from NOC
    if (!empty($nocProjectData['nama_projek_baru'])) {
        $preProjectData['name'] = $nocProjectData['nama_projek_baru'];
    }
    
    if (!empty($nocProjectData['kos_baru'])) {
        $preProjectData['total_cost'] = $nocProjectData['kos_baru'];
        // Recalculate cost breakdown if needed
    }
    
    if (!empty($nocProjectData['agensi_pelaksana_baru'])) {
        $preProjectData['implementing_agency_id'] = $nocProjectData['agensi_pelaksana_baru'];
    }
    
    // Set status and project number
    $preProjectData['status'] = 'Waiting For EPU Approval';
    $preProjectData['project_number'] = $nocProjectData['no_projek']; // Keep original project number
    
    // Create pre-project record
    return PreProject::create($preProjectData);
}
```

**Integration with NOC Submission**:
```php
// In PageController::projectNocSubmit()
public function projectNocSubmit($id)
{
    $noc = Noc::findOrFail($id);
    
    // Validate user can submit
    if ($noc->created_by !== Auth::id()) {
        abort(403, 'Unauthorized');
    }
    
    // Update NOC status
    $noc->update(['status' => 'Pending First Approval']);
    
    // Update project statuses to "NOC"
    foreach ($noc->projects as $project) {
        $project->update(['status' => 'NOC']);
    }
    
    // Process NOC changes and create pre-projects
    $nocToPreProjectService = app(NocToPreProjectService::class);
    $createdPreProjects = $nocToPreProjectService->processNocSubmission($noc);
    
    // Log created pre-projects for tracking
    if (count($createdPreProjects) > 0) {
        Log::info("NOC {$noc->noc_number} created " . count($createdPreProjects) . " pre-project records for EPU approval");
    }
    
    return redirect()->route('pages.project.noc')->with('success', 'NOC submitted successfully');
}
```

## Error Handling

### Project Transfer Errors

**Error**: Pre-project not approved
- **Condition**: Attempting to transfer a pre-project with status other than "Approved"
- **Response**: Throw exception with message "Only approved pre-projects can be transferred"
- **Recovery**: User must approve pre-project first

**Error**: Pre-project already transferred
- **Condition**: Attempting to transfer a pre-project that already has a corresponding project
- **Response**: Return existing project record instead of creating duplicate
- **Recovery**: Automatic - no user action needed

**Error**: Project number generation collision
- **Condition**: Generated project number already exists (race condition)
- **Response**: Retry generation with next sequential number
- **Recovery**: Automatic retry up to 3 attempts

### NOC Import Errors

**Error**: No available projects
- **Condition**: User has no projects available for NOC import
- **Response**: Display message "No projects available for NOC. All projects are either already in NOCs or you have no approved projects."
- **Recovery**: User must wait for pre-projects to be approved and transferred

**Error**: Project already in NOC
- **Condition**: Attempting to add a project that's already in another NOC
- **Response**: Validation error "Project [project_number] is already included in another NOC"
- **Recovery**: User must select different project

**Error**: Invalid project access
- **Condition**: User attempting to add project from different parliament/DUN
- **Response**: Validation error "You don't have permission to add this project"
- **Recovery**: User must select projects from their own parliament/DUN

### Migration Errors

**Error**: Missing project mapping
- **Condition**: noc_pre_project record references pre-project that has no corresponding project
- **Response**: Log error and skip record, continue migration
- **Recovery**: Manual review of skipped records after migration

**Error**: Foreign key constraint violation
- **Condition**: Attempting to update foreign key to non-existent project
- **Response**: Rollback migration, log detailed error
- **Recovery**: Fix data inconsistency and retry migration

**Error**: Duplicate project number
- **Condition**: Generated project number conflicts with existing number
- **Response**: Rollback migration, log error
- **Recovery**: Clear projects table and retry with corrected number generation

### NOC to Pre-Project Integration Errors

**Error**: Missing project data
- **Condition**: NOC project reference is invalid or project deleted
- **Response**: Log error and skip pre-project creation for that project
- **Recovery**: Manual review of skipped projects

**Error**: Invalid agency ID
- **Condition**: New implementing agency ID doesn't exist in master data
- **Response**: Use original agency ID instead, log warning
- **Recovery**: Automatic fallback to original agency

**Error**: Pre-project creation failure
- **Condition**: Database constraint violation or validation error
- **Response**: Log detailed error, continue with other projects
- **Recovery**: Manual review and correction of failed pre-project creation

**Error**: Duplicate project number in pre-projects
- **Condition**: Project number already exists in pre_projects table
- **Response**: Append suffix to project number (e.g., PROJ/2026/001-R1)
- **Recovery**: Automatic suffix generation

### Route Migration Errors

**Error**: Old route accessed
- **Condition**: User accesses old /pages/pre-project/noc/* route
- **Response**: Redirect to new /pages/project/noc/* route with 301 permanent redirect
- **Recovery**: Automatic redirect

**Error**: Missing route parameter
- **Condition**: Route accessed without required {id} parameter
- **Response**: 404 error page
- **Recovery**: User must access route with valid ID

## Testing Strategy

### Dual Testing Approach

This feature requires both unit tests and property-based tests to ensure comprehensive coverage:

**Unit Tests**: Focus on specific examples, edge cases, and integration points
- Test specific project transfer scenarios
- Test NOC creation with known project data
- Test migration with sample data sets
- Test route redirects and responses
- Test UI rendering for specific pages

**Property-Based Tests**: Verify universal properties across all inputs
- Test project number uniqueness across many transfers
- Test data preservation across random pre-projects
- Test status synchronization across various NOC states
- Test access control across different user types
- Test migration integrity across large datasets

### Property-Based Testing Configuration

**Framework**: Use Laravel's built-in testing with a PHP property-based testing library such as `eris/eris` or `giorgiosironi/eris`

**Configuration**:
- Minimum 100 iterations per property test
- Each test tagged with feature name and property number
- Tag format: `@test Feature: noc-system-restructure, Property {number}: {property_text}`

**Example Property Test Structure**:
```php
/**
 * @test
 * Feature: noc-system-restructure, Property 1: Project Number Uniqueness
 */
public function test_project_numbers_are_unique()
{
    $this->forAll(
        Generator\seq(Generator\choose(5, 20))
    )->then(function ($count) {
        // Generate $count project transfers
        $projects = [];
        for ($i = 0; $i < $count; $i++) {
            $preProject = PreProject::factory()->create(['status' => 'Approved']);
            $projects[] = app(ProjectTransferService::class)->transfer($preProject);
        }
        
        // Verify all project numbers are unique
        $projectNumbers = array_map(fn($p) => $p->project_number, $projects);
        $this->assertEquals(count($projectNumbers), count(array_unique($projectNumbers)));
    });
}
```

### Unit Test Coverage

**Project Transfer Tests**:
- Test transfer of approved pre-project creates project
- Test transfer of non-approved pre-project throws exception
- Test transfer of already-transferred pre-project returns existing
- Test project number format matches pattern
- Test all pre-project fields copied to project
- Test timestamps set correctly

**NOC Migration Tests**:
- Test NOC routes redirect from old to new paths
- Test NOC controller methods use projects instead of pre-projects
- Test NOC views render with project data
- Test NOC import loads projects not pre-projects
- Test NOC creation attaches to projects table

**Project Page Tests**:
- Test project page displays three tabs
- Test project tab shows project list
- Test NOC tab shows NOC list
- Test project cancel tab shows placeholder
- Test active tab highlighting

**Status Synchronization Tests**:
- Test NOC submission updates project status to "NOC"
- Test NOC deletion rollbacks project status to "Active"
- Test NOC rejection rollbacks project status to "Active"
- Test project list highlights NOC status projects

**Access Control Tests**:
- Test parliament user sees only their parliament projects
- Test DUN user sees only their DUN projects
- Test NOC import filters by user parliament/DUN
- Test project list filters by user parliament/DUN

**Migration Tests**:
- Test all approved pre-projects transferred to projects
- Test all noc_pre_project records mapped to noc_project
- Test NOC data preserved after migration
- Test referential integrity maintained
- Test migration rollback works correctly

### Integration Tests

**End-to-End Workflow Tests**:
1. Create and approve pre-project → Verify project created
2. Create NOC with project → Verify NOC references project
3. Submit NOC → Verify project status changes
4. Delete NOC → Verify project status rollback
5. Navigate project tabs → Verify correct content displayed

**Database Migration Tests**:
1. Seed database with pre-projects and NOCs
2. Run migration
3. Verify all data migrated correctly
4. Verify all relationships intact
5. Rollback migration
6. Verify data restored

### Test Execution Order

1. **Unit tests first**: Verify individual components work
2. **Property tests**: Verify universal properties hold
3. **Integration tests**: Verify components work together
4. **Migration tests**: Verify data migration works
5. **Manual testing**: Verify UI and user experience

### Success Criteria

All tests must pass before deployment:
- ✅ All unit tests pass (100% of test cases)
- ✅ All property tests pass (100 iterations each)
- ✅ All integration tests pass
- ✅ Migration tests pass with sample data
- ✅ Manual testing confirms UI works correctly
- ✅ No data loss during migration
- ✅ All existing NOC functionality preserved
