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
- `forms.css` - Form inputs, search fields
- `content-header.css` - Content headers with title and description

Main layout styles remain in `public/css/app.css`

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
