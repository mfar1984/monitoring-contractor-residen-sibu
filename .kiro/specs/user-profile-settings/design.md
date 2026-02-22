# Design Document

## Overview

The User Profile and Settings Management feature provides a comprehensive interface for users to manage their personal information, security credentials, and application preferences. The feature consists of two main sections accessible via tab navigation: Profile (for personal information and password management) and Settings (for localization, display, notification, and security preferences).

The design integrates with the existing Laravel authentication system, the IntegrationSetting model for storing user-specific preferences, and the system-wide translation infrastructure. A key architectural decision is the use of user-prefixed settings (type='user_{id}') to ensure complete data isolation between users while leveraging the existing settings storage mechanism.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        User Interface                        │
│  ┌──────────────────────┐  ┌──────────────────────────────┐ │
│  │   Profile Tab        │  │   Settings Tab               │ │
│  │  - Basic Info        │  │  - Account Info (read-only)  │ │
│  │  - Password Change   │  │  - Localization Preferences  │ │
│  │                      │  │  - Notification Preferences  │ │
│  │                      │  │  - Security Info (read-only) │ │
│  └──────────────────────┘  └──────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    PageController                            │
│  - profile()          - settings()                           │
│  - profileUpdate()    - settingsUpdate()                     │
└─────────────────────────────────────────────────────────────┘
                            │
                ┌───────────┴───────────┐
                ▼                       ▼
┌──────────────────────────┐  ┌──────────────────────────────┐
│      User Model          │  │  IntegrationSetting Model    │
│  - Profile fields        │  │  - User-specific settings    │
│  - Password hashing      │  │  - Type: 'user_{id}'         │
│  - Relationships         │  │  - Key-value storage         │
└──────────────────────────┘  └──────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  Translation System                          │
│  - TranslationHelper::trans()                                │
│  - Middleware: ApplyUserLocale                               │
│  - Integration with translation table                        │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

**Profile Update Flow:**
```
User submits form → Validation → Password verification (if changing password)
→ Update User model → Save to database → Redirect with success message
```

**Settings Update Flow:**
```
User submits form → Validation → Store each setting with 'user_{id}' prefix
→ Update session locale → Apply translations → Redirect with success message
```

**Localization Application Flow:**
```
User logs in → Middleware loads user settings → Set app locale
→ Load translations for locale → Apply to all views → Render UI
```

## Components and Interfaces

### 1. PageController

**Responsibilities:**
- Handle HTTP requests for profile and settings pages
- Validate user input
- Coordinate between User model and IntegrationSetting model
- Manage success/error messages

**Methods:**

```php
public function profile(): View
```
- Retrieves authenticated user
- Returns profile view with user data

```php
public function profileUpdate(Request $request): RedirectResponse
```
- Validates profile fields (full_name, email, contact_number, department)
- Validates password fields (current_password, new_password, new_password_confirmation)
- Verifies current password if password change requested
- Updates user record
- Returns redirect with success/error message

```php
public function settings(): View
```
- Retrieves authenticated user
- Loads user-specific settings from IntegrationSetting with type='user_{id}'
- Returns settings view with user and settings data

```php
public function settingsUpdate(Request $request): RedirectResponse
```
- Validates localization preferences (locale, timezone, date_format, time_format, currency, items_per_page)
- Validates notification preferences (email_notifications, sms_notifications, project_updates, approval_notifications)
- Stores each setting with type='user_{id}'
- Updates session locale
- Returns redirect with success message

### 2. User Model

**Responsibilities:**
- Represent user entity
- Manage user authentication
- Store profile information
- Define relationships with category tables

**Key Fields:**
- `id`: Primary key
- `username`: Unique identifier (read-only)
- `full_name`: User's full name
- `email`: User's email address (unique)
- `password`: Hashed password
- `contact_number`: Optional contact number
- `department`: Optional department
- `residen_category_id`: Foreign key to residen_categories
- `agency_category_id`: Foreign key to agency_categories
- `parliament_id`: Foreign key to parliaments
- `dun_id`: Foreign key to duns
- `contractor_category_id`: Foreign key to contractor_categories
- `status`: Account status (Active/Inactive)
- `created_at`: Account creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
```php
public function residenCategory(): BelongsTo
public function agencyCategory(): BelongsTo
public function parliamentCategory(): BelongsTo
public function dunCategory(): BelongsTo
public function contractorCategory(): BelongsTo
```

### 3. IntegrationSetting Model

**Responsibilities:**
- Store user-specific settings as key-value pairs
- Provide methods for getting and setting values
- Support encryption for sensitive fields
- Enable data isolation through type prefixing

**Key Methods:**

```php
public static function setSetting(string $type, string $key, mixed $value): IntegrationSetting
```
- Creates or updates a setting
- Encrypts sensitive fields automatically
- Returns the setting instance

```php
public static function getSetting(string $type, string $key): mixed
```
- Retrieves a single setting value
- Decrypts sensitive fields automatically
- Returns null if not found

```php
public static function getSettings(string $type): array
```
- Retrieves all settings for a given type
- Returns associative array of key-value pairs
- Decrypts sensitive fields automatically

**Storage Format:**
- Type: `'user_{id}'` (e.g., 'user_1', 'user_42')
- Key: Setting name (e.g., 'locale', 'timezone', 'email_notifications')
- Value: Setting value (string, integer, or boolean stored as string)

### 4. TranslationHelper

**Responsibilities:**
- Provide translation lookup functionality
- Support fallback to default language
- Integrate with IntegrationSetting for translation storage

**Methods:**

```php
public static function trans(string $key, ?string $default = null): string
```
- Looks up translation for given key in current locale
- Falls back to default value or key if not found
- Returns translated string

```php
public static function all(): array
```
- Returns all translations for current locale
- Used for bulk translation loading

### 5. ApplyUserLocale Middleware (New Component)

**Responsibilities:**
- Load user-specific locale settings on each request
- Apply locale to application
- Load translations for the selected language

**Implementation:**

```php
public function handle(Request $request, Closure $next): Response
{
    if (Auth::check()) {
        $user = Auth::user();
        $settings = IntegrationSetting::getSettings('user_' . $user->id);
        
        if (isset($settings['locale']) && $settings['locale']) {
            app()->setLocale($settings['locale']);
            session(['locale' => $settings['locale']]);
        }
    }
    
    return $next($request);
}
```

### 6. Blade Views

**Profile View (resources/views/pages/profile.blade.php):**
- Tab navigation component
- Basic Information section with form
- Change Password section with form
- Success/error message display
- Form validation error display

**Settings View (resources/views/pages/settings.blade.php):**
- Tab navigation component
- Account Information section (read-only)
- Localization & Display Preferences section with form
- Notification Preferences section with form
- Security Settings section (read-only)
- Success/error message display

## Data Models

### User Table Schema

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    department VARCHAR(255),
    residen_category_id BIGINT UNSIGNED,
    agency_category_id BIGINT UNSIGNED,
    parliament_id BIGINT UNSIGNED,
    dun_id BIGINT UNSIGNED,
    contractor_category_id BIGINT UNSIGNED,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (residen_category_id) REFERENCES residen_categories(id),
    FOREIGN KEY (agency_category_id) REFERENCES agency_categories(id),
    FOREIGN KEY (parliament_id) REFERENCES parliaments(id),
    FOREIGN KEY (dun_id) REFERENCES duns(id),
    FOREIGN KEY (contractor_category_id) REFERENCES contractor_categories(id)
);
```

### IntegrationSetting Table Schema

```sql
CREATE TABLE integration_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(255) NOT NULL,
    key VARCHAR(255) NOT NULL,
    value TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_type_key (type, key)
);
```

### User Settings Data Model

**Type:** `'user_{id}'` (e.g., 'user_1')

**Localization Settings:**
- `locale`: Language code ('en', 'ms', 'zh')
- `timezone`: Timezone identifier ('Asia/Kuala_Lumpur', 'Asia/Singapore', 'Asia/Bangkok', 'UTC')
- `date_format`: PHP date format string ('d/m/Y', 'm/d/Y', 'Y-m-d', 'd M Y', 'd F Y')
- `time_format`: PHP time format string ('H:i:s', 'h:i A', 'h:i:s A')
- `currency`: Currency code ('MYR', 'USD', 'SGD', 'EUR')
- `items_per_page`: Integer (10, 25, 50, 100)

**Notification Settings:**
- `email_notifications`: Boolean (0 or 1)
- `sms_notifications`: Boolean (0 or 1)
- `project_updates`: Boolean (0 or 1)
- `approval_notifications`: Boolean (0 or 1)

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Profile Update Preserves Username Immutability

*For any* user profile update request, the username field should remain unchanged in the database regardless of any form input.

**Validates: Requirements 1.6**

### Property 2: Password Change Requires Current Password Verification

*For any* password change request, the system should only update the password if the provided current password matches the stored hashed password.

**Validates: Requirements 2.1, 2.2**

### Property 3: Password Hashing Consistency

*For any* successful password change, the new password stored in the database should be a bcrypt hash and should verify against the plaintext password provided by the user.

**Validates: Requirements 2.6**

### Property 4: Email Uniqueness Across Users

*For any* profile update with a new email address, the system should reject the update if another user (excluding the current user) already has that email address.

**Validates: Requirements 1.3**

### Property 5: User Settings Isolation

*For any* two different users, updating settings for user A should not affect the settings retrieved for user B.

**Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5**

### Property 6: Settings Storage Type Prefix Consistency

*For any* user setting stored or retrieved, the type field should always be prefixed with 'user_' followed by the user's ID.

**Validates: Requirements 8.1, 8.2**

### Property 7: Locale Application After Settings Update

*For any* user who updates their locale setting, subsequent requests should have the application locale set to the user's selected language.

**Validates: Requirements 4.12, 10.9**

### Property 8: Translation Fallback to English

*For any* translation key that does not exist in the user's selected language, the system should return the English translation or the key itself.

**Validates: Requirements 10.8**

### Property 9: Notification Preference Boolean Storage

*For any* notification preference checkbox, the stored value should be 1 if checked or 0 if unchecked, never null or any other value.

**Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.7**

### Property 10: Form Validation Preserves User Input

*For any* form submission that fails validation, the form should be re-rendered with all user-provided values preserved in the input fields.

**Validates: Requirements 1.8, 9.2**

### Property 11: User Category Badge Display Consistency

*For any* user with exactly one category assignment (residen, agency, parliament, dun, or contractor), the settings page should display exactly one category badge with the correct color.

**Validates: Requirements 3.2, 11.6**

### Property 12: Password Confirmation Matching

*For any* password change request, the system should only proceed if the new_password and new_password_confirmation fields contain identical values.

**Validates: Requirements 2.4, 2.5**

### Property 13: Translation System Integration

*For any* user with a selected locale, all UI elements including sidebar navigation should display translations from the translation table for that locale.

**Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7**

## Error Handling

### Validation Errors

**Profile Update Validation:**
- Empty full_name: "The full name field is required."
- Invalid email format: "The email must be a valid email address."
- Duplicate email: "The email has already been taken."
- Contact number exceeds 20 characters: "The contact number must not exceed 20 characters."
- Department exceeds 255 characters: "The department must not exceed 255 characters."

**Password Change Validation:**
- Missing current password when new password provided: "The current password field is required when new password is present."
- Incorrect current password: "Current password is incorrect."
- New password less than 8 characters: "The new password must be at least 8 characters."
- Password confirmation mismatch: "The new password confirmation does not match."

**Settings Update Validation:**
- Invalid locale: "The selected locale is invalid."
- Invalid timezone: "The selected timezone is invalid."
- Invalid date format: "The selected date format is invalid."
- Invalid time format: "The selected time format is invalid."
- Invalid currency: "The selected currency is invalid."
- Items per page out of range: "The items per page must be between 10 and 100."

### Database Errors

**Connection Failures:**
- Display generic error message: "Unable to save changes. Please try again later."
- Log detailed error for debugging
- Preserve user input for retry

**Constraint Violations:**
- Unique constraint on email: "The email has already been taken."
- Foreign key constraint: "Invalid user category assignment."

### Authentication Errors

**Unauthenticated Access:**
- Redirect to login page
- Store intended URL for post-login redirect

**Session Expiration:**
- Display message: "Your session has expired. Please log in again."
- Redirect to login page

### Translation Loading Errors

**Missing Translation:**
- Fall back to English translation
- If English translation missing, display translation key
- Log missing translation for admin review

**Translation Table Unavailable:**
- Fall back to hardcoded English strings
- Log error for investigation
- Continue operation without translations

## Testing Strategy

### Unit Tests

**Profile Update Tests:**
- Test successful profile update with valid data
- Test profile update with invalid email format
- Test profile update with duplicate email
- Test profile update with empty required fields
- Test profile update preserves username immutability

**Password Change Tests:**
- Test successful password change with correct current password
- Test password change rejection with incorrect current password
- Test password change with short new password (< 8 characters)
- Test password change with mismatched confirmation
- Test password hashing produces valid bcrypt hash

**Settings Update Tests:**
- Test successful settings update with valid localization preferences
- Test successful settings update with valid notification preferences
- Test settings update with invalid locale value
- Test settings update with invalid timezone value
- Test checkbox handling for unchecked notification preferences

**User Settings Isolation Tests:**
- Test that updating user A's settings does not affect user B's settings
- Test that retrieving user A's settings returns only user A's data
- Test that settings are stored with correct 'user_{id}' prefix

### Property-Based Tests

Each property test should run a minimum of 100 iterations with randomized inputs.

**Property Test 1: Username Immutability**
- **Feature: user-profile-settings, Property 1: Profile Update Preserves Username Immutability**
- Generate random user with random username
- Generate random profile update data
- Update profile
- Assert username remains unchanged

**Property Test 2: Password Verification**
- **Feature: user-profile-settings, Property 2: Password Change Requires Current Password Verification**
- Generate random user with random password
- Generate random incorrect current password
- Attempt password change
- Assert password change is rejected

**Property Test 3: Password Hashing**
- **Feature: user-profile-settings, Property 3: Password Hashing Consistency**
- Generate random user
- Generate random new password
- Change password
- Assert stored password is bcrypt hash
- Assert hash verifies against plaintext password

**Property Test 4: Email Uniqueness**
- **Feature: user-profile-settings, Property 4: Email Uniqueness Across Users**
- Generate two random users with different emails
- Attempt to update user A's email to user B's email
- Assert update is rejected

**Property Test 5: Settings Isolation**
- **Feature: user-profile-settings, Property 5: User Settings Isolation**
- Generate two random users
- Set random settings for user A
- Set different random settings for user B
- Assert user A's settings match what was set for user A
- Assert user B's settings match what was set for user B

**Property Test 6: Type Prefix Consistency**
- **Feature: user-profile-settings, Property 6: Settings Storage Type Prefix Consistency**
- Generate random user
- Set random setting
- Query database directly
- Assert type field equals 'user_{id}'

**Property Test 7: Locale Application**
- **Feature: user-profile-settings, Property 7: Locale Application After Settings Update**
- Generate random user
- Set random locale ('en', 'ms', or 'zh')
- Make subsequent request as that user
- Assert app locale matches user's selected locale

**Property Test 8: Translation Fallback**
- **Feature: user-profile-settings, Property 8: Translation Fallback to English**
- Generate random translation key that doesn't exist in Chinese
- Set user locale to Chinese
- Request translation
- Assert returns English translation or key

**Property Test 9: Boolean Storage**
- **Feature: user-profile-settings, Property 9: Notification Preference Boolean Storage**
- Generate random notification preference states (checked/unchecked)
- Save settings
- Query database directly
- Assert all notification values are either 0 or 1

**Property Test 10: Input Preservation**
- **Feature: user-profile-settings, Property 10: Form Validation Preserves User Input**
- Generate random invalid profile data
- Submit form
- Assert form re-renders with same input values

**Property Test 11: Category Badge Display**
- **Feature: user-profile-settings, Property 11: User Category Badge Display Consistency**
- Generate random user with one category assignment
- Render settings page
- Assert exactly one category badge is displayed
- Assert badge color matches category type

**Property Test 12: Password Confirmation**
- **Feature: user-profile-settings, Property 12: Password Confirmation Matching**
- Generate random user
- Generate random new password
- Generate different random confirmation password
- Attempt password change
- Assert change is rejected

**Property Test 13: Translation Integration**
- **Feature: user-profile-settings, Property 13: Translation System Integration**
- Generate random user with Chinese locale
- Load sidebar navigation
- Assert all navigation labels use Chinese translations from translation table

### Integration Tests

**End-to-End Profile Flow:**
- User logs in
- Navigates to Profile page
- Updates full name and email
- Submits form
- Verifies success message
- Verifies database updated
- Verifies changes reflected in UI

**End-to-End Settings Flow:**
- User logs in
- Navigates to Settings page
- Changes language to Chinese
- Changes timezone to Asia/Singapore
- Enables email notifications
- Submits form
- Verifies success message
- Verifies settings saved to database
- Verifies UI displays in Chinese
- Verifies sidebar displays Chinese translations

**End-to-End Password Change Flow:**
- User logs in
- Navigates to Profile page
- Enters current password
- Enters new password and confirmation
- Submits form
- Verifies success message
- Logs out
- Logs in with new password
- Verifies login successful

### UI/UX Tests

**Tab Navigation:**
- Click Profile tab, verify Profile content displayed
- Click Settings tab, verify Settings content displayed
- Verify active tab highlighted correctly

**Form Reset:**
- Enter data in form
- Click Reset button
- Verify form reloaded with original values

**Live Preview:**
- Select different date formats
- Verify preview updates with current date in selected format
- Select different time formats
- Verify preview updates with current time in selected format

**Error Display:**
- Submit invalid data
- Verify error messages displayed in red alert box
- Verify specific field errors shown
- Verify input values preserved

**Success Message:**
- Submit valid data
- Verify success message displayed in green alert box
- Verify message disappears after page reload

### Security Tests

**Password Security:**
- Verify passwords stored as bcrypt hashes
- Verify current password required for password change
- Verify password minimum length enforced

**Data Isolation:**
- Verify user A cannot access user B's settings
- Verify settings queries filtered by user ID
- Verify no cross-user data leakage

**Authentication:**
- Verify unauthenticated users redirected to login
- Verify authenticated users can access profile and settings
- Verify session expiration handled correctly

**Input Sanitization:**
- Test XSS prevention in text fields
- Test SQL injection prevention in form inputs
- Test CSRF token validation on form submissions
