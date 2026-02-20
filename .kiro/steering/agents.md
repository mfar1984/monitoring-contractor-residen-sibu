# Agent Guidelines

## Database Safety Rules

### CRITICAL: Database Operations

**NEVER run these commands without explicit user permission:**

- ❌ `php artisan db:wipe`
- ❌ `php artisan db:wipe --force`
- ❌ `php artisan migrate:fresh`
- ❌ `php artisan migrate:fresh --seed`
- ❌ `DROP DATABASE`
- ❌ `TRUNCATE TABLE`
- ❌ Any command that deletes or wipes database data

**These commands will delete ALL user data including:**
- Master data entries
- User accounts
- Configuration settings
- All records in the system

**Safe Database Operations:**

✅ `php artisan migrate` - Run new migrations only
✅ `php artisan migrate:rollback --step=1` - Rollback last migration only
✅ Creating new migrations
✅ Adding columns to existing tables

**If migration fails:**
1. Fix the migration file
2. Use `php artisan migrate:rollback --step=1` to rollback the failed migration
3. Run `php artisan migrate` again
4. NEVER use `db:wipe` or `migrate:fresh`

## UI/UX Standards

### Icons and Visual Elements

- **DO NOT use emoji icons** in the user interface
- Use plain text, SVG icons, or icon fonts instead
- Emojis are inconsistent across platforms and unprofessional

### Examples

❌ **Bad:**
```html
<span class="breadcrumb-icon">🏠</span>
<a href="/">Home</a>
```

✅ **Good:**
```html
<a href="/">Home</a>
```

Or with SVG/icon font:
```html
<i class="icon-home"></i>
<a href="/">Home</a>
```

## Layout Standards

### Header and Navigation Structure

- Header must remain at the top without overflow
- Breadcrumb must stay below header in a separate row
- Sidebar logo should span the combined height of header + breadcrumb
- Use CSS Grid with `grid-row: span 2` for sidebar logo to align properly
- Separator lines must align across sidebar and content areas

### Grid Layout Best Practices

- Use CSS Grid for consistent column alignment
- Sidebar width: 250px
- Use `grid-template-columns: 250px 1fr` for sidebar + content layout
- Ensure borders align across grid cells

## Code Organization

- Follow component-based structure
- Each component in its own folder
- Keep code clean and organized
- Follow Laravel best practices

## CSS Component Structure

### File Organization

CSS files are organized by component in `public/css/components/`:

- `tabs.css` - Tab navigation styling
- `table.css` - Data tables, status badges, action buttons
- `pagination.css` - Pagination controls and showing info
- `buttons.css` - All button styles (primary, reset, logout)
- `forms.css` - Form inputs, dropdowns, radio buttons, checkboxes, textareas
- `content-header.css` - Content headers with title and description

Main layout styles remain in `public/css/app.css`

### Forms Component (`forms.css`)

The forms component provides consistent styling for all form elements:

**Text Inputs:**
- Standard height: 34px
- Padding: 8px 12px
- Border: 1px solid #e0e0e0
- Border radius: 4px
- Focus state: Blue border with subtle shadow

**Dropdowns (Select):**
- Same height as text inputs: 34px
- Custom arrow icon (no default browser arrow)
- Padding: 8px 32px 8px 12px (extra space for arrow)
- Support for optgroup styling
- Multiple select support

**Radio Buttons:**
- Size: 18px x 18px
- Accent color: #007bff (blue)
- Horizontal layout with 20px gap
- Focus state: Blue outline

**Checkboxes:**
- Size: 18px x 18px
- Accent color: #007bff (blue)
- Vertical layout with 10px gap
- Focus state: Blue outline

**Textareas:**
- Min height: 80px
- Vertical resize only
- Same styling as text inputs

**File Inputs:**
- Custom file selector button
- Hover state on button

**Validation States:**
- Error: Red border (#dc3545)
- Success: Green border (#28a745)
- Focus maintains validation color

**Usage Example:**
```html
<div class="form-group">
    <label for="name">Name <span class="required">*</span></label>
    <input type="text" id="name" name="name" placeholder="Enter name">
    <span class="form-help">Please enter your full name</span>
</div>

<div class="form-group">
    <label for="category">Category</label>
    <select id="category" name="category">
        <option value="">Select Category</option>
        <option value="1">Category 1</option>
    </select>
</div>

<div class="form-group">
    <label>Status</label>
    <div class="radio-group">
        <div class="radio-option">
            <input type="radio" id="active" name="status" value="Active">
            <label for="active">Active</label>
        </div>
        <div class="radio-option">
            <input type="radio" id="inactive" name="status" value="Inactive">
            <label for="inactive">Inactive</label>
        </div>
    </div>
</div>
```

### Loading CSS Files

All component CSS files must be loaded in the layout:

```html
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/tabs.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/table.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/pagination.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/content-header.css') }}">
```

**Important:** Do NOT use `@import` in CSS files - it doesn't work reliably in Laravel. Always load CSS files directly in the layout.

## Blade Components

### Data Table Component

Location: `resources/views/components/data-table.blade.php`

A reusable component for displaying data tables with search, pagination, and actions.

**Usage:**
```blade
<x-data-table
    title="Page Title"
    description="Page description text"
    createButtonText="Create User"
    createButtonRoute="#"
    searchPlaceholder="Search..."
    :columns="['Column1', 'Column2', 'Column3']"
    :data="$collection"
    :rowsPerPage="5"
>
    @foreach($collection as $item)
    <tr>
        <td>{{ $item->field }}</td>
        <!-- more columns -->
    </tr>
    @endforeach
</x-data-table>
```

**Features:**
- Automatic search functionality
- Smart pagination (shows max 3 page numbers with ellipsis)
- Reset button
- Showing entries info
- Fully responsive

### Pagination Design

- Active page: circular button with blue background
- Inactive pages: no border, text only
- Hover: text color changes to blue
- Format: `< << 1 2 3 ... 11 >> >`
- Maximum 3 consecutive page numbers shown
- Ellipsis (...) for skipped pages

### Master Data Tabs Component

Location: `resources/views/components/master-data-tabs.blade.php`

A reusable component for Master Data tab navigation with horizontal drag scrolling.

**Usage:**
```blade
<x-master-data-tabs active="residen" />
```

**Features:**
- Horizontal drag/touch scrolling (no scroll buttons)
- Hidden scrollbar for clean appearance
- Smooth scroll behavior
- Responsive design
- Active tab highlighting

**Available Tabs:**
- residen
- agency
- parliaments
- duns
- contractor
- status
- project-category
- division
- district
- land-title-status
- project-ownership
- implementation-method
- noc-note

## Adding New Components

When creating new components:

1. Create CSS file in `public/css/components/[component-name].css`
2. Add link tag in `resources/views/layouts/app.blade.php`
3. Create Blade component in `resources/views/components/[component-name].blade.php`
4. Document usage in this file

## Best Practices

- Keep CSS files focused on single component
- Use consistent naming conventions
- Avoid inline styles
- Use Material Symbols for icons
- Font size: 12px maximum
- Follow existing color scheme:
  - Primary blue: #007bff
  - Success green: #28a745
  - Danger red: #dc3545
  - Warning yellow: #ffc107
  - Text: #333333
  - Light text: #666666
  - Border: #e0e0e0
  - Background: #ffffff

## Master Data Integration System

### Purpose

The Master Data and Users ID integration system is designed to establish user identity and project access control based on organizational categories.

### System Architecture

#### Master Data Categories

Master Data serves as the central repository for organizational entities:

- **Residen**: Administrator/Residen categories
- **Agency**: Government agencies (DID, JKR, JBAB, Sarawak Waterboard, etc.)
- **Member of Parliament**: Parliamentary constituencies (DUN Nangka, DUN Bawang Assan, Parlimen Sibu, etc.)
- **Contractor**: Contractor companies with registration details
- **Status**: Project status definitions

#### Users ID Integration

Each user account is linked to one Master Data category through foreign key relationships:

- `users.residen_category_id` → `residen_categories.id`
- `users.agency_category_id` → `agency_categories.id`
- `users.parliament_category_id` → `parliament_categories.id`
- `users.contractor_category_id` → `contractor_categories.id`

### Project Access Control (Future Implementation)

When projects are added to the system:

1. **Project Tagging**: Each project will be tagged with Master Data categories (Agency, Parliament, Contractor, etc.)
2. **Automatic Access**: Users automatically see projects tagged with their assigned Master Data category
3. **Filtered Views**: Project lists are filtered based on user's category assignment
4. **Hierarchical Access**: Users under a specific Master Data category only see projects relevant to their organization

### Implementation Guidelines

#### Creating Users

When creating users in Users ID:
- Select the appropriate Master Data category from the dropdown
- The dropdown only shows Active categories from Master Data
- Each user must be assigned to exactly one category type

#### Project Display Logic (Future)

```php
// Example: Get projects for logged-in user
$user = Auth::user();

if ($user->agency_category_id) {
    $projects = Project::where('agency_category_id', $user->agency_category_id)->get();
} elseif ($user->parliament_category_id) {
    $projects = Project::where('parliament_category_id', $user->parliament_category_id)->get();
} elseif ($user->contractor_category_id) {
    $projects = Project::where('contractor_category_id', $user->contractor_category_id)->get();
}
```

#### Database Relationships

**User Model Relationships:**
```php
public function residenCategory()
{
    return $this->belongsTo(ResidenCategory::class);
}

public function agencyCategory()
{
    return $this->belongsTo(AgencyCategory::class);
}

public function parliamentCategory()
{
    return $this->belongsTo(ParliamentCategory::class);
}

public function contractorCategory()
{
    return $this->belongsTo(ContractorCategory::class);
}
```

### Benefits

1. **Centralized Management**: Master Data provides single source of truth for organizational entities
2. **Automatic Access Control**: Users automatically inherit project visibility based on their category
3. **Scalability**: Easy to add new categories or modify existing ones
4. **Data Integrity**: Foreign key constraints ensure valid category assignments
5. **Audit Trail**: Clear tracking of which users belong to which organizations

### Best Practices

- Always create Master Data categories before creating users
- Keep Master Data categories Active to appear in user creation dropdowns
- Use descriptive names and codes for easy identification
- Regularly review and update Master Data to reflect organizational changes
- When projects are implemented, ensure proper tagging with Master Data categories

## Integration Settings Security

### Purpose

Integration settings store sensitive configuration data such as API keys, passwords, and secrets. This system ensures all sensitive data is encrypted before storage and decrypted only when needed.

### Security Implementation

#### Automatic Encryption

The system automatically encrypts sensitive fields when saving to database:

- **Email Configuration**: `smtp_password`
- **SMS Configuration**: `api_key`
- **Webhook Configuration**: `webhook_secret`
- **API Configuration**: `api_key`, `api_secret`
- **Weather Configuration**: `api_key`

#### Encryption Rules

Fields containing these keywords are automatically encrypted:
- `password`
- `api_key`
- `secret`
- `smtp_password`
- `api_secret`
- `webhook_secret`

#### Database Storage

```php
// Example: Saving encrypted data
IntegrationSetting::setSetting('email', 'smtp_password', 'mypassword123');
// Stored in database as encrypted string

// Example: Retrieving decrypted data
$settings = IntegrationSetting::getSettings('email');
// Returns decrypted password for use
```

#### Model Implementation

The `IntegrationSetting` model handles encryption/decryption automatically:

```php
public static function setSetting($type, $key, $value)
{
    // Automatically encrypts sensitive fields
    $sensitiveFields = ['password', 'api_key', 'secret', 'smtp_password', 'api_secret', 'webhook_secret'];
    
    if (shouldEncrypt($key, $sensitiveFields) && $value) {
        $value = encrypt($value);
    }
    
    return self::updateOrCreate(['type' => $type, 'key' => $key], ['value' => $value]);
}

public static function getSettings($type)
{
    // Automatically decrypts sensitive fields when retrieved
    // Returns plain text for use in application
}
```

### Test Functionality

#### Test Modals

Each integration page includes a Test button that opens a modal:

- **Email**: Test modal with email input field to send test email
- **SMS**: Test modal with phone number input to send test SMS
- **Webhook**: Test modal to send test payload to webhook endpoint
- **API**: Test modal to verify API connection
- **Weather**: Test modal to verify OpenWeatherMap API connection

#### Test Button Behavior

- Test button opens modal with appropriate input fields
- Modal includes validation for required fields
- Test functionality is currently disabled (shows warning message)
- Modal can be closed by clicking Cancel, X button, or clicking outside modal

#### Implementation Notes

- Test functionality does not actually send requests (disabled for security)
- Configuration must be saved before testing
- Test results are displayed within the modal
- No sensitive data is exposed in test results

### Security Best Practices

1. **Never Display Sensitive Data**: API keys and passwords should never be displayed in plain text in the UI
2. **Use Encryption**: All sensitive configuration data is encrypted at rest in the database
3. **Secure Transmission**: Always use HTTPS for transmitting sensitive data
4. **Access Control**: Only authorized users should access integration settings
5. **Audit Logging**: Consider logging all changes to integration settings for audit purposes
6. **Key Rotation**: Regularly rotate API keys and passwords
7. **Environment Variables**: For production, consider using environment variables for critical secrets

### Future Enhancements

- Implement actual test functionality with proper error handling
- Add audit logging for configuration changes
- Implement role-based access control for integration settings
- Add configuration backup and restore functionality
- Implement API key rotation reminders


## NOC (Notice of Change) System

**IMPLEMENTATION STATUS: COMPLETE** ✅

All NOC pages have been implemented with user-based access control and budget tracking:
- ✅ NOC List page with data-table component
- ✅ NOC Create page with Import + Add New functionality and real-time budget tracking
- ✅ NOC Detail page with approval workflow
- ✅ NOC Print page with A4 landscape format
- ✅ User access control (Parliament/DUN auto-detection)
- ✅ Budget tracking and calculation
- ✅ Two-level approval workflow

### CRITICAL: Design Consistency Rule

**ALL NOC pages MUST use the SAME design pattern as Pre-Project pages:**

- ✅ Use `<x-data-table>` component for list views
- ✅ Use consistent form styling
- ✅ Use tabs for navigation between Pre-Project and NOC
- ✅ Follow existing color scheme and spacing
- ❌ DO NOT create custom table layouts
- ❌ DO NOT deviate from Pre-Project design patterns

### Overview

The NOC (Notice of Change) system allows Member of Parliament users to create NOC documents that contain multiple pre-projects. These NOCs require a two-level approval process from Residen users.

### NOC Structure

- **One NOC** can contain **multiple Pre-Projects**
- Each NOC has a unique NOC number (format: NOC/YYYY/###)
- NOCs are associated with either a Parliament or DUN

### Approval Workflow

1. **Draft**: NOC is created by Member of Parliament user
2. **Submit**: NOC is submitted for approval
3. **First Approval**: Approved by First Approver (Residen user)
4. **Second Approval**: Approved by Second Approver (Residen user)
5. **Final Status**: Approved or Rejected

### Approval Settings

Approval settings are configured in **Application Settings** (`/pages/general/application`):

- **First Approval**: Select Residen user who will be the first approver
- **Second Approval**: Select Residen user who will be the second approver

These settings are stored in `integration_settings` table with keys:
- `first_approval_user`
- `second_approval_user`

### Database Structure

#### Tables

1. **nocs** - Main NOC table
   - `id`
   - `noc_number` - Unique NOC identifier (format: NOC/YYYY/###)
   - `parliament_id` - Foreign key to parliaments (auto-detected from logged-in user)
   - `dun_id` - Foreign key to duns (auto-detected from logged-in user)
   - `noc_date` - Date of NOC
   - `created_by` - User who created the NOC
   - `status` - Draft, Pending First Approval, Pending Second Approval, Approved, Rejected
   - `first_approver_id` - First approver user ID
   - `first_approved_at` - First approval timestamp
   - `first_approval_remarks` - First approval remarks
   - `second_approver_id` - Second approver user ID
   - `second_approved_at` - Second approval timestamp
   - `second_approval_remarks` - Second approval remarks

2. **noc_pre_project** - Pivot table (many-to-many) with project change details
   - `id`
   - `noc_id` - Foreign key to nocs
   - `pre_project_id` - Foreign key to pre_projects
   - `tahun_rtp` - RTP Year
   - `no_projek` - Project Number
   - `nama_projek_asal` - Original Project Name (auto-filled from pre_project)
   - `nama_projek_baru` - New Project Name (if changed)
   - `kos_asal` - Original Cost (auto-filled from pre_project)
   - `kos_baru` - New Cost (if changed)
   - `agensi_pelaksana_asal` - Original Implementing Agency (auto-filled)
   - `agensi_pelaksana_baru` - New Implementing Agency (if changed)
   - `noc_note_id` - Foreign key to noc_notes (reason for change)

3. **noc_notes** - Master data for NOC change reasons
   - `id`
   - `name` - Note name (e.g., "Change of Project Scope")
   - `code` - Unique code
   - `description` - Description
   - `status` - Active/Inactive

### User Access Control

**CRITICAL**: NOC system uses user-based access control:

- Users with `parliament_id` can only see and create NOCs for their Parliament's projects
- Users with `dun_id` can only see and create NOCs for their DUN's projects
- Parliament/DUN is automatically detected from logged-in user - NO manual selection
- Pre-projects are filtered based on user's Parliament/DUN assignment

### Page Structure

#### 1. NOC List Page (`/pages/pre-project/noc`)

**MUST use `<x-data-table>` component** - same as Pre-Project page

```blade
<x-data-table
    title="NOC (Notice of Change)"
    description="Manage Notice of Change documents for pre-projects."
    createButtonText="Create NOC"
    createButtonRoute="{{ route('pages.pre-project.noc.create') }}"
    searchPlaceholder="Search NOC..."
    :columns="['NOC Number', 'Parliament/DUN', 'Date', 'Projects', 'Status', 'Actions']"
    :data="$nocs"
    :rowsPerPage="10"
>
    <!-- Table rows here -->
</x-data-table>
```

**Columns:**
- NOC Number
- Parliament/DUN (auto-detected from user)
- Date
- Projects Count
- Status (with color coding)
- Actions (View, Print)

#### 2. NOC Create Page (`/pages/pre-project/noc/create`)

**IMPLEMENTATION COMPLETE** - Uses table format with Import + Add New buttons

**Key Features:**
- ❌ NO Parliament/DUN dropdown (auto-detected from logged-in user)
- ✅ Date picker for NOC date
- ✅ **Import Project button**: Opens modal to select existing pre-projects
- ✅ **Add New Project button**: Adds empty row for brand new projects
- ✅ **Table format**: 1 row = 1 project (easier to edit than expandable forms)
- ✅ **Budget Summary Box**: Purple gradient box with real-time calculations

**Table Columns:**
- **Tahun RTP** (required) - RTP Year
- **No Projek** (required) - Project Number
- **Nama Projek Asal** - Original project name (read-only for imported, editable for new)
- **Nama Projek Baru** - New project name (optional)
- **Kos Asal (RM)** - Original cost (read-only for imported, editable for new)
- **Kos Baru (RM)** - New cost (optional, triggers budget calculation)
- **Agensi Asal** - Original agency (read-only for imported, editable for new)
- **Agensi Baru** - New agency (dropdown from master data)
- **Catatan** (required) - NOC Note dropdown
- **Actions** - Delete button

**Import Modal:**
- Simple display showing only Project Name and Total Cost
- Checkbox selection for multiple projects
- Import button adds selected projects to table

**Budget Tracking Logic:**
- Import projects → Kos Asal added to total original budget
- Enter Kos Baru → Deducted from total budget
- Empty Kos Baru = project cancelled (budget freed)
- Remaining budget = Total original - Total allocated
- Budget box turns red if over budget
- Real-time JavaScript calculation as values change

**Example Budget Flow:**
1. Import 2 projects: RM 2,058,000 + RM 752,000 = RM 2,810,000 total
2. Set Kos Baru RM 1,500,000 = RM 1,310,000 remaining
3. Add new project RM 500,000 = RM 810,000 remaining

**Form Validation:**
- At least one project must be added
- All required fields must be filled
- Budget constraints are visual only (no hard limit)

#### 3. NOC Detail Page (`/pages/pre-project/noc/{id}`)

**IMPLEMENTATION COMPLETE**

**Features:**
- Shows NOC information (number, date, parliament/dun, created by, status)
- Budget summary box (purple gradient) with:
  - Total Original Budget
  - Total New Budget
  - Budget Difference (red if negative)
- Project changes table showing:
  - Tahun RTP, No Projek
  - Original vs New project name (blue highlight for changes)
  - Original vs New cost (blue highlight for changes)
  - Original vs New agency (blue highlight for changes)
  - NOC Note (reason for change)
- Approval history section (if submitted)
- Action buttons based on user permissions:
  - **Submit for Approval** (if Draft and created by user)
  - **Approve/Reject** (if pending and user is authorized approver)
- Back to List and Print buttons

#### 4. NOC Print Page (`/pages/pre-project/noc/{id}/print`)

**IMPLEMENTATION COMPLETE**

**Features:**
- A4 landscape format with print-optimized styling
- Header with NOC title, number, and Parliament/DUN
- Info section with NOC details (number, date, parliament/dun, status)
- Budget summary box with totals
- Full project table with all columns:
  - Bil (numbering)
  - Tahun RTP, No Projek
  - Nama Projek Asal, Nama Projek Baru
  - Kos Asal, Kos Baru
  - Agensi Pelaksana Asal, Agensi Pelaksana Baru
  - Catatan
- Signature sections for both approvers:
  - Shows actual signatures if approved
  - Shows blank signature lines if not yet approved
- Print and Close buttons (hidden when printing)
- Footer with print timestamp

### Routes

```php
Route::get('/pages/pre-project/noc', 'preProjectNoc') // List
Route::get('/pages/pre-project/noc/create', 'preProjectNocCreate') // Create form
Route::post('/pages/pre-project/noc', 'preProjectNocStore') // Store
Route::get('/pages/pre-project/noc/{id}', 'preProjectNocShow') // Detail
Route::post('/pages/pre-project/noc/{id}/submit', 'preProjectNocSubmit') // Submit for approval
Route::post('/pages/pre-project/noc/{id}/approve', 'preProjectNocApprove') // Approve
Route::post('/pages/pre-project/noc/{id}/reject', 'preProjectNocReject') // Reject
Route::get('/pages/pre-project/noc/{id}/print', 'preProjectNocPrint') // Print view
Route::delete('/pages/pre-project/noc/{id}', 'preProjectNocDelete') // Delete (Draft only)
```

### Pre-Project Status Integration

**IMPLEMENTATION COMPLETE** ✅

When a NOC is submitted or deleted, the system automatically updates the status of related pre-projects:

#### Status Flow:
1. **Draft NOC Created**: Pre-projects remain "Active" (no status change)
2. **NOC Submitted**: All imported pre-projects status changes to "NOC"
3. **NOC Deleted (Draft only)**: All imported pre-projects status rollback to "Active"

#### Pre-Project List Display:
- Projects with status "NOC" are highlighted with red background (#ffe6e6)
- Status badge shows "NOC" in red (#dc3545)
- Edit and Delete buttons are disabled (greyed out) for "NOC" status projects
- Projects remain visible in the list for tracking purposes

#### Delete Functionality:
- Delete button only appears for NOCs with "Draft" status
- Deleting a NOC will:
  - Rollback all imported pre-projects status to "Active"
  - Delete NOC attachments from storage
  - Delete NOC record and pivot table entries
- Submitted/Approved NOCs cannot be deleted

### Authorization Rules

#### Who Can Create NOC?
- Member of Parliament users (users with `parliament_category_id`)

#### Who Can Approve NOC?
- **First Approval**: User ID specified in Application Settings (`first_approval_user`)
- **Second Approval**: User ID specified in Application Settings (`second_approval_user`)

### Approval Logic

```php
// First Approval
if ($noc->status === 'Pending First Approval' && $user->id == $firstApprover) {
    // Approve and move to Pending Second Approval
    $noc->update([
        'status' => 'Pending Second Approval',
        'first_approver_id' => $user->id,
        'first_approved_at' => now(),
        'first_approval_remarks' => $request->remarks,
    ]);
}

// Second Approval
if ($noc->status === 'Pending Second Approval' && $user->id == $secondApprover) {
    // Approve and mark as Approved (final)
    $noc->update([
        'status' => 'Approved',
        'second_approver_id' => $user->id,
        'second_approved_at' => now(),
        'second_approval_remarks' => $request->remarks,
    ]);
}
```

### Status Color Coding

Use consistent status badge styling:

- **Draft**: `style="background-color: #f5f5f5; color: #666;"`
- **Pending First Approval**: `style="background-color: #fff3cd; color: #856404;"`
- **Pending Second Approval**: `style="background-color: #cce5ff; color: #004085;"`
- **Approved**: `class="status-badge status-active"` (green)
- **Rejected**: `style="background-color: #f8d7da; color: #721c24;"`

### Best Practices

1. **Always use data-table component** for list views - DO NOT create custom tables
2. **Follow existing design patterns** from Pre-Project pages exactly
3. **Validate approval permissions** before allowing approval actions
4. **Generate unique NOC numbers** automatically using `Noc::generateNocNumber()`
5. **Only show available pre-projects** in create form (exclude projects already in NOCs)
6. **Provide clear status indicators** with proper color coding
7. **Include print functionality** for approved NOCs

### Common Mistakes to Avoid

❌ Creating custom table layouts instead of using `<x-data-table>`
❌ Using different styling from Pre-Project pages
❌ Not validating approval permissions
❌ Allowing same pre-project in multiple NOCs
❌ Inconsistent status color coding

## Comprehensive Code Issue Resolution Checklist

### CRITICAL: When Solving Code Issues

**ALWAYS check ALL related components to ensure nothing is missed:**

When fixing bugs or implementing features, you MUST verify and update ALL of the following components where applicable. Missing even one component can cause system-wide failures.

### 1. Routes (`routes/web.php` or `routes/api.php`)

**Check:**
- ✅ Are all required routes defined?
- ✅ Do route names match controller method calls?
- ✅ Are route parameters correctly defined?
- ✅ Is middleware applied correctly?
- ✅ For web routes: Are they in the correct route group?
- ✅ For API routes: Are they versioned correctly?

**Example:**
```php
// Web routes
Route::get('/pages/project/noc', [PageController::class, 'projectNoc'])->name('pages.project.noc');
Route::post('/pages/project/noc', [PageController::class, 'projectNocStore'])->name('pages.project.noc.store');

// API routes
Route::prefix('v1')->group(function () {
    Route::get('/projects', [ProjectApiController::class, 'index']);
});
```

### 2. Controllers (`app/Http/Controllers/`)

**Check:**
- ✅ Does the controller method exist?
- ✅ Are all required parameters passed to views?
- ✅ Is data validation handled correctly?
- ✅ Are relationships eager-loaded to avoid N+1 queries?
- ✅ Is error handling implemented?
- ✅ Are success/error messages returned?
- ✅ Is authorization checked (policies/gates)?

**Example:**
```php
public function projectNocCreate()
{
    // Check authorization
    $this->authorize('create', Noc::class);
    
    // Get data with relationships
    $projects = Noc::getAvailableProjects(Auth::user());
    $agencies = AgencyCategory::where('status', 'Active')->get();
    $nocNotes = NocNote::where('status', 'Active')->get();
    
    return view('pages.project-noc-create', compact('projects', 'agencies', 'nocNotes'));
}
```

### 3. Models (`app/Models/`)

**Check:**
- ✅ Are all fillable fields defined?
- ✅ Are relationships (belongsTo, hasMany, belongsToMany) correctly defined?
- ✅ Are casts defined for dates, booleans, JSON fields?
- ✅ Are custom methods/scopes implemented?
- ✅ Is the correct table name specified (if not following convention)?
- ✅ Are pivot table relationships using correct table names?
- ✅ Are accessor/mutator methods needed?

**Example:**
```php
class Noc extends Model
{
    protected $fillable = ['noc_number', 'parliament_id', 'status'];
    
    protected $casts = [
        'noc_date' => 'date',
        'first_approved_at' => 'datetime',
    ];
    
    // Relationships
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'noc_project')
            ->withPivot(['tahun_rtp', 'kos_asal', 'kos_baru'])
            ->withTimestamps();
    }
}
```

### 4. Database Migrations (`database/migrations/`)

**Check:**
- ✅ Are all required columns created?
- ✅ Are column types correct (string, integer, decimal, date, etc.)?
- ✅ Are foreign keys defined with proper constraints?
- ✅ Are indexes added for frequently queried columns?
- ✅ Are nullable fields marked correctly?
- ✅ Are default values set where needed?
- ✅ Is the down() method implemented for rollback?
- ✅ Have migrations been run? (`php artisan migrate`)

**Example:**
```php
Schema::create('noc_project', function (Blueprint $table) {
    $table->id();
    $table->foreignId('noc_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->string('tahun_rtp');
    $table->decimal('kos_asal', 15, 2);
    $table->decimal('kos_baru', 15, 2)->nullable();
    $table->timestamps();
    
    // Add index for faster queries
    $table->index(['noc_id', 'project_id']);
});
```

### 5. Database Seeders (`database/seeders/`)

**Check:**
- ✅ Are seeders created for master data?
- ✅ Are seeders created for test data?
- ✅ Do seeders check for existing data before inserting?
- ✅ Are relationships properly seeded?
- ✅ Are seeders registered in DatabaseSeeder.php?
- ✅ Can seeders be run multiple times safely?

**Example:**
```php
class NocNoteSeeder extends Seeder
{
    public function run()
    {
        $notes = [
            ['name' => 'Change of Scope', 'code' => 'SCOPE', 'status' => 'Active'],
            ['name' => 'Budget Adjustment', 'code' => 'BUDGET', 'status' => 'Active'],
        ];
        
        foreach ($notes as $note) {
            NocNote::firstOrCreate(['code' => $note['code']], $note);
        }
    }
}
```

### 6. Blade Views (`resources/views/`)

**Check:**
- ✅ Are all variables passed from controller available?
- ✅ Are Blade components used correctly?
- ✅ Are form action routes correct?
- ✅ Are CSRF tokens included in forms?
- ✅ Are old() values used for form repopulation?
- ✅ Are error messages displayed?
- ✅ Are success messages displayed?
- ✅ Is the layout extended correctly?
- ✅ Are sections defined correctly?

**Example:**
```blade
@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('pages.project.noc.store') }}">
    @csrf
    
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </div>
    @endif
    
    <input type="text" name="noc_number" value="{{ old('noc_number') }}">
</form>
@endsection
```

### 7. Form Requests (`app/Http/Requests/`)

**Check:**
- ✅ Are validation rules defined?
- ✅ Are custom error messages provided?
- ✅ Is authorization logic implemented?
- ✅ Are conditional validation rules handled?
- ✅ Are custom validation rules created if needed?

**Example:**
```php
class StoreNocRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::user()->can('create', Noc::class);
    }
    
    public function rules()
    {
        return [
            'noc_date' => 'required|date',
            'projects' => 'required|array|min:1',
            'projects.*.tahun_rtp' => 'required|string',
            'projects.*.kos_baru' => 'nullable|numeric|min:0',
        ];
    }
    
    public function messages()
    {
        return [
            'projects.required' => 'At least one project must be added',
            'projects.*.tahun_rtp.required' => 'RTP Year is required for all projects',
        ];
    }
}
```

### 8. Services/Actions (`app/Services/` or `app/Actions/`)

**Check:**
- ✅ Is complex business logic extracted from controllers?
- ✅ Are services reusable across multiple controllers?
- ✅ Is error handling implemented?
- ✅ Are database transactions used where needed?
- ✅ Are services testable?

**Example:**
```php
class NocService
{
    public function createNoc($data, $user)
    {
        DB::beginTransaction();
        try {
            $noc = Noc::create([
                'noc_number' => Noc::generateNocNumber(),
                'parliament_id' => $user->parliament_id,
                'noc_date' => $data['noc_date'],
                'created_by' => $user->id,
                'status' => 'Draft',
            ]);
            
            // Attach projects
            foreach ($data['projects'] as $project) {
                $noc->projects()->attach($project['project_id'], [
                    'tahun_rtp' => $project['tahun_rtp'],
                    'kos_asal' => $project['kos_asal'],
                    'kos_baru' => $project['kos_baru'],
                ]);
            }
            
            DB::commit();
            return $noc;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### 9. Helpers (`app/Helpers/`)

**Check:**
- ✅ Are helper functions registered in composer.json?
- ✅ Are helper functions namespaced or global?
- ✅ Are helper functions documented?
- ✅ Are helper functions tested?

**Example:**
```php
// app/Helpers/FormatHelper.php
if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        return 'RM ' . number_format($amount, 2);
    }
}

// composer.json
"autoload": {
    "files": [
        "app/Helpers/FormatHelper.php"
    ]
}
```

### 10. Middleware (`app/Http/Middleware/`)

**Check:**
- ✅ Is middleware registered in Kernel.php?
- ✅ Is middleware applied to correct routes?
- ✅ Does middleware handle unauthorized access correctly?
- ✅ Are middleware parameters passed correctly?

**Example:**
```php
class CheckNocPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::user()->can($permission, Noc::class)) {
            abort(403, 'Unauthorized action.');
        }
        
        return $next($request);
    }
}

// In routes
Route::get('/noc/create', [NocController::class, 'create'])
    ->middleware('check.noc.permission:create');
```

### 11. Configuration Files (`config/` and `.env`)

**Check:**
- ✅ Are API keys stored in .env?
- ✅ Are config values cached? (`php artisan config:cache`)
- ✅ Are sensitive values never committed to git?
- ✅ Is .env.example updated with new variables?

**Example:**
```php
// config/noc.php
return [
    'max_projects_per_noc' => env('NOC_MAX_PROJECTS', 50),
    'approval_levels' => env('NOC_APPROVAL_LEVELS', 2),
];

// .env
NOC_MAX_PROJECTS=50
NOC_APPROVAL_LEVELS=2
```

### 12. API Resources (`app/Http/Resources/`)

**Check:**
- ✅ Are API responses formatted consistently?
- ✅ Are relationships included when needed?
- ✅ Are sensitive fields hidden?
- ✅ Are resource collections used for lists?

**Example:**
```php
class NocResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'noc_number' => $this->noc_number,
            'status' => $this->status,
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

### 13. Service Providers (`app/Providers/`)

**Check:**
- ✅ Are custom services registered?
- ✅ Are view composers registered?
- ✅ Are custom validation rules registered?
- ✅ Are observers registered?

**Example:**
```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register observer
        Noc::observe(NocObserver::class);
        
        // Register view composer
        View::composer('pages.project-noc-*', function ($view) {
            $view->with('agencies', AgencyCategory::active()->get());
        });
    }
}
```

### 14. Jobs and Queues (`app/Jobs/`)

**Check:**
- ✅ Are long-running tasks queued?
- ✅ Are job retries configured?
- ✅ Are failed jobs handled?
- ✅ Is queue worker running?

**Example:**
```php
class ProcessNocApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $timeout = 120;
    
    public function handle()
    {
        // Send approval notifications
        // Update related records
        // Generate reports
    }
}

// Dispatch job
ProcessNocApproval::dispatch($noc);
```

### 15. Mail/Notifications (`app/Mail/` or `app/Notifications/`)

**Check:**
- ✅ Are email templates created?
- ✅ Are notification channels configured?
- ✅ Are notification preferences respected?
- ✅ Are notifications queued for performance?

**Example:**
```php
class NocApprovedNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('NOC Approved')
            ->line('Your NOC has been approved.')
            ->action('View NOC', url('/pages/project/noc/' . $this->noc->id));
    }
}
```

### 16. Tests (`tests/Feature/` or `tests/Unit/`)

**Check:**
- ✅ Are feature tests written for main workflows?
- ✅ Are unit tests written for complex logic?
- ✅ Are edge cases tested?
- ✅ Are tests passing? (`php artisan test`)

**Example:**
```php
class NocCreationTest extends TestCase
{
    public function test_user_can_create_noc()
    {
        $user = User::factory()->create(['parliament_id' => 1]);
        $project = Project::factory()->create(['parliament_id' => 1]);
        
        $response = $this->actingAs($user)->post('/pages/project/noc', [
            'noc_date' => now()->format('Y-m-d'),
            'projects' => [
                ['project_id' => $project->id, 'tahun_rtp' => '2024']
            ]
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('nocs', ['created_by' => $user->id]);
    }
}
```

### 17. Localization (`lang/` or `resources/lang/`)

**Check:**
- ✅ Are translation keys used instead of hardcoded text?
- ✅ Are all supported languages provided?
- ✅ Are validation messages translated?
- ✅ Are pluralization rules handled?

**Example:**
```php
// lang/en/noc.php
return [
    'created' => 'NOC created successfully',
    'approved' => 'NOC approved successfully',
    'projects_count' => '{0} No projects|{1} 1 project|[2,*] :count projects',
];

// In blade
{{ __('noc.created') }}
{{ trans_choice('noc.projects_count', $count) }}
```

### 18. Assets (`public/css/`, `public/js/`, `resources/`)

**Check:**
- ✅ Are CSS files compiled? (`npm run build`)
- ✅ Are JavaScript files compiled?
- ✅ Are assets versioned for cache busting?
- ✅ Are component CSS files loaded in layout?
- ✅ Are custom scripts included in correct sections?

**Example:**
```blade
{{-- In layout --}}
<link rel="stylesheet" href="{{ asset('css/components/forms.css') }}">

@push('scripts')
<script>
    // Custom JavaScript for this page
</script>
@endpush
```

### Integration Verification Checklist

When implementing or fixing a feature, verify ALL of these:

1. ✅ **Routes**: Defined and named correctly
2. ✅ **Controllers**: Methods exist with correct logic
3. ✅ **Models**: Relationships and fillable fields correct
4. ✅ **Migrations**: Tables and columns created
5. ✅ **Seeders**: Master data populated
6. ✅ **Views**: Variables available and forms correct
7. ✅ **Form Requests**: Validation rules defined
8. ✅ **Services**: Business logic extracted
9. ✅ **Helpers**: Reusable functions created
10. ✅ **Middleware**: Security checks applied
11. ✅ **Config**: Settings in .env and config files
12. ✅ **API Resources**: JSON responses formatted
13. ✅ **Service Providers**: Custom services registered
14. ✅ **Jobs**: Long tasks queued
15. ✅ **Notifications**: Users notified of actions
16. ✅ **Tests**: Feature and unit tests passing
17. ✅ **Localization**: Text translated
18. ✅ **Assets**: CSS/JS compiled and loaded

### Related Page Integration

**CRITICAL**: When fixing a feature, check if it affects other pages:

- ✅ Does this change affect list pages?
- ✅ Does this change affect detail/show pages?
- ✅ Does this change affect create/edit forms?
- ✅ Does this change affect print/export pages?
- ✅ Does this change affect dashboard/statistics?
- ✅ Does this change affect related models/tables?
- ✅ Does this change affect API endpoints?

**Example**: When fixing NOC import to use `projects` table instead of `pre_projects`:
- ✅ Update `Noc::getAvailableProjects()` method
- ✅ Update pivot table name from `noc_pre_project` to `noc_project`
- ✅ Update Blade view to show `project_number` field
- ✅ Update JavaScript to populate `project_number`
- ✅ Update form field name from `pre_project_id` to `project_id`
- ✅ Update controller to handle `project_id` instead of `pre_project_id`
- ✅ Update NOC detail page to show correct project data
- ✅ Update NOC print page to show correct project data

### MySQL Database Verification

**ALWAYS verify database state:**

```sql
-- Check if table exists
SHOW TABLES LIKE 'noc_project';

-- Check table structure
DESCRIBE noc_project;

-- Check if data exists
SELECT * FROM noc_project LIMIT 10;

-- Check relationships
SELECT n.*, p.* 
FROM nocs n 
LEFT JOIN noc_project np ON n.id = np.noc_id 
LEFT JOIN projects p ON np.project_id = p.id;

-- Check for orphaned records
SELECT * FROM noc_project WHERE project_id NOT IN (SELECT id FROM projects);
```

### Final Verification Steps

Before marking an issue as resolved:

1. ✅ Run migrations: `php artisan migrate`
2. ✅ Clear cache: `php artisan cache:clear`
3. ✅ Clear config: `php artisan config:clear`
4. ✅ Clear views: `php artisan view:clear`
5. ✅ Compile assets: `npm run build`
6. ✅ Run tests: `php artisan test`
7. ✅ Check browser console for JavaScript errors
8. ✅ Check Laravel logs: `storage/logs/laravel.log`
9. ✅ Test in browser with actual user flow
10. ✅ Verify database records are created correctly

### Common Integration Mistakes

❌ **Updating model but forgetting to update migration**
❌ **Updating controller but forgetting to update routes**
❌ **Updating view but forgetting to pass data from controller**
❌ **Renaming database table but forgetting to update model relationships**
❌ **Adding new field but forgetting to add to fillable array**
❌ **Creating new page but forgetting to add navigation link**
❌ **Updating API but forgetting to update frontend JavaScript**
❌ **Adding validation but forgetting to display error messages**
❌ **Creating seeder but forgetting to register in DatabaseSeeder**
❌ **Adding CSS but forgetting to load in layout**

### Remember

**"Fix one thing, check everything related"** - A single change can affect multiple components. Always trace the full flow from route → controller → model → database → view and back.


## User Access Control & Data Isolation System

### CRITICAL: Data Isolation Rules

**IMPLEMENTATION STATUS: COMPLETE** ✅

The system implements strict data isolation based on user categories. Each user type can ONLY see and manage data related to their assigned organization.

### User Categories & Master Data

The system has 4 main user categories, each linked to their respective Master Data:

#### 1. RESIDEN (Administrator)
**Master Data Pages:**
- `/pages/master-data/residen` - Residen categories management
- `/pages/users-id/residen` - Residen user accounts

**Access Level:**
- ✅ **FULL ACCESS** to ALL data across the system
- ✅ Can view and manage ALL pre-projects, projects, and NOCs
- ✅ Exclusive access to **System Settings** section
- ✅ Can approve Pre-Projects and NOCs

**Database Fields:**
- `users.residen_category_id` → `residen_categories.id`

**Special Privileges:**
- Only Residen users can access System Settings and all sub-sections
- Can be assigned as Pre-Project Approvers
- Can be assigned as NOC First/Second Approvers

---

#### 2. AGENCY (Government Agencies)
**Master Data Pages:**
- `/pages/master-data/agency` - Agency categories (DID, JKR, JBAB, etc.)
- `/pages/users-id/agency` - Agency user accounts

**Access Level:**
- ⚠️ **ISOLATED** - Can ONLY see data for their assigned Agency
- ✅ Can view/manage Pre-Projects where `agency_category_id` matches their agency
- ✅ Can view/manage Projects where `agency_category_id` matches their agency
- ✅ Can view/manage NOCs that contain projects from their agency
- ❌ CANNOT access System Settings

**Database Fields:**
- `users.agency_category_id` → `agency_categories.id`
- `pre_projects.agency_category_id` → `agency_categories.id`
- `projects.agency_category_id` → `agency_categories.id`

**Filtering Logic:**
```php
if ($user->agency_category_id) {
    // Pre-Projects filtering
    $query->where('agency_category_id', $user->agency_category_id);
    
    // Projects filtering
    $query->where('agency_category_id', $user->agency_category_id);
    
    // NOCs filtering (through projects relationship)
    $query->whereHas('projects', function($q) use ($user) {
        $q->where('agency_category_id', $user->agency_category_id);
    });
}
```

**Example:**
- User: `did@did.gov.my` (Agency: DID)
- Can ONLY see Pre-Projects/Projects with `agency_category_id = DID`
- CANNOT see data from JKR, JBAB, or other agencies

---

#### 3. PARLIAMENT / DUN (Member of Parliament)
**Master Data Pages:**
- `/pages/master-data/parliaments` - Parliament constituencies
- `/pages/master-data/duns` - DUN (State Assembly) constituencies
- `/pages/users-id/parliament` - Parliament/DUN user accounts

**Access Level:**
- ⚠️ **ISOLATED** - Can ONLY see data for their assigned Parliament or DUN
- ✅ Can view/manage Pre-Projects for their Parliament/DUN
- ✅ Can view/manage Projects for their Parliament/DUN
- ✅ Can create and view NOCs for their Parliament/DUN
- ❌ CANNOT access System Settings

**Database Fields:**
- `users.parliament_id` → `parliaments.id` (for Parliament users)
- `users.dun_id` → `duns.id` (for DUN users)
- `pre_projects.parliament_id` → `parliaments.id`
- `pre_projects.dun_basic_id` → `duns.id`
- `projects.parliament_id` → `parliaments.id`
- `projects.dun_basic_id` → `duns.id`
- `nocs.parliament_id` → `parliaments.id`
- `nocs.dun_id` → `duns.id`

**Filtering Logic:**
```php
if ($user->parliament_id) {
    // Parliament user - filter by parliament_id
    $query->where('parliament_id', $user->parliament_id);
} elseif ($user->dun_id) {
    // DUN user - filter by dun_basic_id
    $query->where('dun_basic_id', $user->dun_id);
}
```

**Example:**
- User: `wongsoonkoh@sibu.org.my` (DUN: Bawang Assan)
- Can ONLY see Pre-Projects/Projects with `dun_basic_id = Bawang Assan`
- CANNOT see data from DUN Nangka or other constituencies

---

#### 4. CONTRACTOR
**Master Data Pages:**
- `/pages/master-data/contractor` - Contractor companies
- `/pages/users-id/contractor` - Contractor user accounts

**Access Level:**
- ⚠️ **ISOLATED** - Can ONLY see data for their assigned Contractor company
- ✅ Can view Projects assigned to their contractor company
- ❌ CANNOT access System Settings
- ❌ CANNOT create or edit Pre-Projects/Projects (view-only access)

**Database Fields:**
- `users.contractor_category_id` → `contractor_categories.id`
- `projects.contractor_category_id` → `contractor_categories.id` (future implementation)

**Note:** Contractor isolation is planned for future implementation when contractor assignment to projects is added.

---

### Pages with Data Isolation

**CRITICAL:** The following pages MUST implement data isolation filtering:

#### ✅ Pre-Project Pages
- `/pages/pre-project` - Pre-Project list
  - Parliament users: Filter by `parliament_id`
  - DUN users: Filter by `dun_basic_id`
  - Agency users: Filter by `agency_category_id`
  - Residen users: See ALL

#### ✅ Project Pages
- `/pages/project` - Active projects list
  - Parliament users: Filter by `parliament_id`
  - DUN users: Filter by `dun_basic_id`
  - Agency users: Filter by `agency_category_id`
  - Residen users: See ALL

#### ✅ Project Cancel Pages
- `/pages/project-cancel` - Cancelled projects list
  - Parliament users: Filter by `parliament_id`
  - DUN users: Filter by `dun_basic_id`
  - Agency users: Filter by `agency_category_id`
  - Residen users: See ALL

#### ✅ NOC Pages
- `/pages/project/noc` - NOC list
  - Parliament users: Filter by `parliament_id`
  - DUN users: Filter by `dun_id`
  - Agency users: Filter through `projects` relationship
  - Residen users: See ALL

---

### System Settings Access Control

**CRITICAL:** System Settings is EXCLUSIVE to Residen users and Admin only.

**System Settings Pages:**
- `/pages/general/application` - Application settings
- `/pages/general/approver` - Approver settings
- `/pages/general/translation` - Translation settings
- `/pages/general/localization` - Localization settings
- `/pages/general/maintenance` - Maintenance mode
- `/pages/integrations/*` - All integration settings (Email, SMS, Webhook, API, Weather)

**Access Rules:**
- ✅ Residen users: FULL ACCESS
- ❌ Agency users: NO ACCESS
- ❌ Parliament/DUN users: NO ACCESS
- ❌ Contractor users: NO ACCESS

**Implementation:**
```php
// In middleware or controller
if (!$user->residen_category_id) {
    abort(403, 'Unauthorized access. System Settings is only accessible to Residen users.');
}
```

---

### Controller Implementation Pattern

**CRITICAL:** ALL controllers that display Pre-Projects, Projects, or NOCs MUST follow this pattern:

```php
public function index()
{
    $user = auth()->user();
    
    // Start query
    $query = Model::query();
    
    // Apply access control filter
    if ($user->parliament_id) {
        // Parliament user - filter by parliament_id
        $query->where('parliament_id', $user->parliament_id);
    } elseif ($user->dun_id) {
        // DUN user - filter by dun_basic_id
        $query->where('dun_basic_id', $user->dun_id);
    } elseif ($user->agency_category_id) {
        // Agency user - filter by agency_category_id
        $query->where('agency_category_id', $user->agency_category_id);
    }
    // Residen users see ALL (no filter applied)
    
    $data = $query->get();
    
    return view('page', compact('data'));
}
```

**For NOC filtering (special case):**
```php
public function projectNoc()
{
    $user = auth()->user();
    
    $nocsQuery = Noc::with(['parliament', 'dun', 'creator', 'projects']);
    
    if ($user->parliament_id) {
        $nocsQuery->where('parliament_id', $user->parliament_id);
    } elseif ($user->dun_id) {
        $nocsQuery->where('dun_id', $user->dun_id);
    } elseif ($user->agency_category_id) {
        // Agency filtering through projects relationship
        $nocsQuery->whereHas('projects', function($query) use ($user) {
            $query->where('agency_category_id', $user->agency_category_id);
        });
    }
    
    $nocs = $nocsQuery->get();
    
    return view('pages.project-noc', compact('nocs'));
}
```

---

### Testing Data Isolation

**CRITICAL:** When implementing new features, ALWAYS test with different user types:

1. **Test with Residen user:**
   - Should see ALL data
   - Should have access to System Settings

2. **Test with Agency user (e.g., `did@did.gov.my`):**
   - Should ONLY see data for DID agency
   - Should NOT see data from JKR, JBAB, etc.
   - Should NOT access System Settings

3. **Test with Parliament user:**
   - Should ONLY see data for their Parliament
   - Should NOT see data from other Parliaments

4. **Test with DUN user (e.g., `wongsoonkoh@sibu.org.my`):**
   - Should ONLY see data for their DUN (e.g., Bawang Assan)
   - Should NOT see data from other DUNs (e.g., Nangka)

---

### Common Mistakes to Avoid

❌ **Forgetting to add filtering in controller methods**
❌ **Using wrong field name** (e.g., `dun_id` instead of `dun_basic_id`)
❌ **Not handling all user types** (Parliament, DUN, Agency, Residen)
❌ **Allowing non-Residen users to access System Settings**
❌ **Not testing with different user accounts**
❌ **Filtering NOCs by direct field instead of through relationship**

---

### Database Field Reference

**Users Table:**
- `residen_category_id` - Links to `residen_categories.id`
- `agency_category_id` - Links to `agency_categories.id`
- `parliament_id` - Links to `parliaments.id`
- `dun_id` - Links to `duns.id`
- `contractor_category_id` - Links to `contractor_categories.id`

**Pre-Projects Table:**
- `parliament_id` - Links to `parliaments.id`
- `dun_basic_id` - Links to `duns.id` (NOT `dun_id`)
- `agency_category_id` - Links to `agency_categories.id`

**Projects Table:**
- `parliament_id` - Links to `parliaments.id`
- `dun_basic_id` - Links to `duns.id` (NOT `dun_id`)
- `agency_category_id` - Links to `agency_categories.id`

**NOCs Table:**
- `parliament_id` - Links to `parliaments.id`
- `dun_id` - Links to `duns.id`
- **NO `agency_category_id`** - Filter through `projects` relationship

---

### Summary

**Key Principles:**
1. ✅ **Residen users** = FULL ACCESS to everything including System Settings
2. ⚠️ **Agency users** = ISOLATED by `agency_category_id`
3. ⚠️ **Parliament users** = ISOLATED by `parliament_id`
4. ⚠️ **DUN users** = ISOLATED by `dun_basic_id` (NOT `dun_id`)
5. ⚠️ **Contractor users** = ISOLATED by `contractor_category_id` (future)
6. ❌ **System Settings** = Residen and Admin ONLY

**Remember:** When implementing ANY new feature that displays Pre-Projects, Projects, or NOCs, you MUST implement data isolation filtering based on user category. Failure to do so will result in data leakage across organizations.


## Database Verification Before Coding

### CRITICAL: Always Verify Database Structure First

**⚠️ THIS IS A GENERAL RULE FOR ALL DATABASE OPERATIONS ⚠️**

This rule applies to:
- ✅ ALL tables (users, pre_projects, projects, nocs, etc.)
- ✅ ALL columns (full_name, username, email, status, etc.)
- ✅ ALL relationships (belongsTo, hasMany, belongsToMany, etc.)
- ✅ ALL queries (SELECT, INSERT, UPDATE, DELETE)
- ✅ ALL features (new or existing)

**NEVER make assumptions about database structure.** Before writing ANY code that interacts with the database, you MUST verify:

1. ✅ **Table exists** in the database
2. ✅ **Column exists** in the table
3. ✅ **Column name is correct** (exact spelling, case-sensitive)
4. ✅ **Data exists** in the table (if querying)
5. ✅ **Relationships are correct** (foreign keys, pivot tables)
6. ✅ **Data types are correct** (string, integer, decimal, date, etc.)

### When to Apply This Rule

**ALWAYS verify BEFORE:**
- Creating new features
- Modifying existing features
- Adding new database queries
- Updating models
- Writing controllers
- Creating views that display database data
- Adding validation rules
- Creating seeders
- Writing tests

**If something doesn't exist:**
- ❌ DO NOT assume it exists
- ✅ CREATE migration to add it
- ✅ RUN migration
- ✅ VERIFY it was created
- ✅ THEN write code to use it

### How to Verify Database Structure

#### Step 1: Check Migration Files

**ALWAYS read the migration files first** to understand the database structure:

```bash
# Find migration for specific table
grep -r "create_users_table" database/migrations/
grep -r "Schema::create('users'" database/migrations/

# Find migration that adds specific column
grep -r "full_name" database/migrations/
```

**Example:**
```php
// File: database/migrations/2026_02_14_082247_add_user_details_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->string('full_name')->after('username');  // ✅ Column name is 'full_name'
    $table->string('email')->after('full_name');
});
```

#### Step 2: Verify in Database (if needed)

If migration files are unclear, verify directly in database:

```sql
-- Check if table exists
SHOW TABLES LIKE 'users';

-- Check table structure and column names
DESCRIBE users;

-- Check if column exists
SHOW COLUMNS FROM users LIKE 'full_name';

-- Check if data exists
SELECT full_name FROM users LIMIT 5;
```

#### Step 3: Check Model Fillable/Casts

Verify the model has the correct field definitions:

```php
// app/Models/User.php
class User extends Model
{
    protected $fillable = [
        'username',
        'full_name',  // ✅ Must match database column name
        'email',
        'password',
    ];
}
```

---

### Common Database Mistakes

❌ **Assuming column name without checking**
```php
// WRONG - Assuming column is 'name'
Auth::user()->name  // ❌ Column doesn't exist!
```

✅ **Verify column name first**
```php
// CORRECT - Checked migration, column is 'full_name'
Auth::user()->full_name  // ✅ Column exists
```

---

❌ **Using wrong field name**
```php
// WRONG - Using 'dun_id' instead of 'dun_basic_id'
$query->where('dun_id', $user->dun_id);  // ❌ Wrong field name!
```

✅ **Use correct field name from migration**
```php
// CORRECT - Checked migration, field is 'dun_basic_id'
$query->where('dun_basic_id', $user->dun_id);  // ✅ Correct field name
```

---

❌ **Assuming relationship exists**
```php
// WRONG - Assuming NOC has agency_category_id
$query->where('agency_category_id', $user->agency_category_id);  // ❌ Column doesn't exist in NOCs table!
```

✅ **Check migration, use correct relationship**
```php
// CORRECT - NOCs table doesn't have agency_category_id, filter through projects
$query->whereHas('projects', function($q) use ($user) {
    $q->where('agency_category_id', $user->agency_category_id);
});  // ✅ Correct relationship
```

---

### Verification Checklist

Before writing code that uses database fields, verify:

1. ✅ **Read migration file** to confirm table structure
2. ✅ **Check column name** (exact spelling, case-sensitive)
3. ✅ **Verify field exists** in the table
4. ✅ **Check Model fillable** array includes the field
5. ✅ **Test query** in database if unsure

### Real-World Example: Header Welcome Message

**User Request:** "Change header from email to Full Name"

**WRONG Approach (Assuming):**
```php
// ❌ Assuming column is 'name' without checking
<span>Welcome, {{ Auth::user()->name }}</span>
```

**CORRECT Approach (Verify First):**

1. **Check migration:**
```bash
grep -r "full_name" database/migrations/
```

2. **Found migration:**
```php
// database/migrations/2026_02_14_082247_add_user_details_to_users_table.php
$table->string('full_name')->after('username');  // ✅ Column is 'full_name'
```

3. **Use correct field:**
```php
// ✅ Verified column name is 'full_name'
<span>Welcome, {{ Auth::user()->full_name }}</span>
```

---

### Database Safety Rules

1. **NEVER assume** column names - always verify
2. **ALWAYS read** migration files before coding
3. **CHECK relationships** before using whereHas/with
4. **VERIFY data exists** before querying
5. **TEST queries** in database if unsure

### Remember

**"Verify first, code second"** - Taking 2 minutes to check the database structure will save hours of debugging and prevent bugs in production.

**Database structure is the source of truth.** Migration files define what exists in the database. Always consult them before writing code.
