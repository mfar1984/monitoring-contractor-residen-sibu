# Requirements Document

## Introduction

The User Profile and Settings Management feature enables users to manage their personal information, customize their account preferences, and control notification settings. This feature provides a centralized location for users to update their profile details, change passwords, configure display preferences, and manage how they receive system notifications. The feature is accessible to all user categories (Residen, Agency, Parliament, DUN, Contractor) and ensures data isolation through user-specific settings storage.

## Glossary

- **User**: An authenticated account holder in the Laravel Monitoring System
- **Profile**: Personal information associated with a user account (full name, email, contact number, department)
- **Settings**: User-specific preferences for localization, display, and notifications
- **User_Category**: The organizational classification of a user (Residen, Agency, Parliament, DUN, Contractor)
- **Integration_Settings_Table**: Database table storing user-specific settings with type prefix "user_{id}"
- **Tab_Navigation**: UI pattern with Profile and Settings tabs for switching between sections
- **Password_Verification**: Security mechanism requiring current password before allowing password changes
- **Data_Isolation**: Principle ensuring each user's settings are stored separately and do not affect other users
- **Localization**: User preferences for language, timezone, date format, time format, and currency
- **Notification_Preferences**: User choices for receiving email, SMS, project updates, and approval notifications

## Requirements

### Requirement 1: Profile Information Management

**User Story:** As a user, I want to view and update my profile information, so that I can keep my personal details current in the system.

#### Acceptance Criteria

1. WHEN a user accesses the Profile page, THE System SHALL display the user's current profile information including username, full name, email, contact number, and department
2. WHEN a user updates their full name, THE System SHALL validate it is not empty and does not exceed 255 characters
3. WHEN a user updates their email, THE System SHALL validate it is a valid email format, does not exceed 255 characters, and is unique across all users except the current user
4. WHEN a user updates their contact number, THE System SHALL accept optional input up to 20 characters
5. WHEN a user updates their department, THE System SHALL accept optional input up to 255 characters
6. WHEN a user views their username, THE System SHALL display it as read-only with a message indicating it cannot be changed
7. WHEN a user successfully updates their profile, THE System SHALL save the changes to the database and display a success message
8. WHEN profile validation fails, THE System SHALL display error messages and preserve the user's input for correction

### Requirement 2: Password Change Management

**User Story:** As a user, I want to change my password securely, so that I can maintain account security and update credentials when needed.

#### Acceptance Criteria

1. WHEN a user attempts to change their password, THE System SHALL require the current password for verification
2. WHEN a user enters an incorrect current password, THE System SHALL reject the password change and display an error message
3. WHEN a user enters a new password, THE System SHALL validate it is at least 8 characters long
4. WHEN a user enters a new password, THE System SHALL require password confirmation that matches the new password exactly
5. WHEN password confirmation does not match, THE System SHALL reject the password change and display an error message
6. WHEN a user successfully changes their password, THE System SHALL hash the new password using secure hashing and save it to the database
7. WHEN a user successfully changes their password, THE System SHALL display a success message
8. WHEN a user submits the profile form without password fields, THE System SHALL update other profile information without requiring password verification

### Requirement 3: Account Information Display

**User Story:** As a user, I want to view my account information and user category, so that I can verify my account details and organizational assignment.

#### Acceptance Criteria

1. WHEN a user accesses the Settings page, THE System SHALL display read-only account information including username, full name, email, contact number, and department
2. WHEN a user views their account information, THE System SHALL display their user category badge with appropriate color coding (Residen: blue, Agency: green, Parliament: yellow, DUN: cyan, Contractor: gray)
3. WHEN a user views their account information, THE System SHALL display the account creation timestamp in formatted date-time
4. WHEN a user views their account information, THE System SHALL display the last updated timestamp in formatted date-time
5. WHEN a user has no email, contact number, or department, THE System SHALL display a dash (-) for those fields

### Requirement 4: Localization Preferences Management

**User Story:** As a user, I want to customize my language, timezone, and format preferences, so that I can view information in my preferred format and language.

#### Acceptance Criteria

1. WHEN a user selects a language preference, THE System SHALL accept English, Bahasa Melayu, or Chinese as valid options
2. WHEN a user changes their language preference, THE System SHALL apply translations from the system translation table (/pages/general/translation) for the selected language
3. WHEN a user changes their language preference, THE System SHALL update all UI elements including sidebar, navigation, and page content to display in the selected language
4. WHEN a user selects a timezone preference, THE System SHALL accept Asia/Kuala_Lumpur, Asia/Singapore, Asia/Bangkok, or UTC as valid options
5. WHEN a user selects a date format preference, THE System SHALL accept d/m/Y, m/d/Y, Y-m-d, d M Y, or d F Y as valid options
6. WHEN a user views date format options, THE System SHALL display a live preview showing the current date in each format
7. WHEN a user selects a time format preference, THE System SHALL accept H:i:s, h:i A, or h:i:s A as valid options
8. WHEN a user views time format options, THE System SHALL display a live preview showing the current time in each format
9. WHEN a user selects a currency preference, THE System SHALL accept MYR, USD, SGD, or EUR as valid options
10. WHEN a user selects items per page preference, THE System SHALL accept 10, 25, 50, or 100 as valid options
11. WHEN a user saves localization preferences, THE System SHALL store them in the integration_settings table with type prefix "user_{id}"
12. WHEN a user saves localization preferences, THE System SHALL set the application locale to the selected language for subsequent requests
13. WHEN a user saves localization preferences, THE System SHALL display a success message

### Requirement 5: Notification Preferences Management

**User Story:** As a user, I want to control how I receive notifications, so that I can manage communication preferences according to my needs.

#### Acceptance Criteria

1. WHEN a user toggles email notifications, THE System SHALL store the preference as enabled (1) or disabled (0)
2. WHEN a user toggles SMS notifications, THE System SHALL store the preference as enabled (1) or disabled (0)
3. WHEN a user toggles project updates notifications, THE System SHALL store the preference as enabled (1) or disabled (0)
4. WHEN a user toggles approval notifications, THE System SHALL store the preference as enabled (1) or disabled (0)
5. WHEN a user saves notification preferences, THE System SHALL store them in the integration_settings table with type prefix "user_{id}"
6. WHEN a user saves notification preferences, THE System SHALL display a success message
7. WHEN notification checkboxes are not checked during form submission, THE System SHALL store them as disabled (0)

### Requirement 6: Security Information Display

**User Story:** As a user, I want to view my security settings and receive security tips, so that I can maintain awareness of my account security status.

#### Acceptance Criteria

1. WHEN a user views security settings, THE System SHALL display the last password change timestamp
2. WHEN a user views security settings, THE System SHALL display their account status with color-coded badge (Active: green, Inactive: red)
3. WHEN a user views security settings, THE System SHALL display security tips including password best practices and logout recommendations
4. WHEN a user views security settings, THE System SHALL display all information as read-only

### Requirement 7: Tab Navigation and UI Consistency

**User Story:** As a user, I want to navigate between Profile and Settings pages using tabs, so that I can easily switch between related sections.

#### Acceptance Criteria

1. WHEN a user accesses the Profile page, THE System SHALL display tab navigation with Profile and Settings tabs
2. WHEN a user accesses the Settings page, THE System SHALL display tab navigation with Profile and Settings tabs
3. WHEN a user is on the Profile page, THE System SHALL highlight the Profile tab as active
4. WHEN a user is on the Settings page, THE System SHALL highlight the Settings tab as active
5. WHEN a user clicks the Profile tab, THE System SHALL navigate to the Profile page
6. WHEN a user clicks the Settings tab, THE System SHALL navigate to the Settings page
7. WHEN tabs are displayed, THE System SHALL use consistent styling matching the application design pattern

### Requirement 8: Data Isolation and User-Specific Storage

**User Story:** As a system administrator, I want user settings to be isolated per user, so that each user's preferences do not affect other users.

#### Acceptance Criteria

1. WHEN the System stores user settings, THE System SHALL use the type prefix "user_{id}" where {id} is the user's unique identifier
2. WHEN the System retrieves user settings, THE System SHALL query only settings with type "user_{id}" for the authenticated user
3. WHEN a user updates their settings, THE System SHALL only modify settings associated with their user ID
4. WHEN a user views their settings, THE System SHALL only display settings associated with their user ID
5. WHEN multiple users update settings simultaneously, THE System SHALL ensure each user's settings remain isolated and do not conflict

### Requirement 9: Form Validation and Error Handling

**User Story:** As a user, I want clear validation messages when I submit invalid data, so that I can correct errors and successfully update my information.

#### Acceptance Criteria

1. WHEN form validation fails, THE System SHALL display all validation errors in a red alert box at the top of the page
2. WHEN form validation fails, THE System SHALL preserve the user's input in form fields for correction
3. WHEN a user successfully submits a form, THE System SHALL display a success message in a green alert box at the top of the page
4. WHEN a user clicks the Reset button, THE System SHALL reload the page and discard unsaved changes
5. WHEN validation errors occur, THE System SHALL display specific error messages for each field that failed validation

### Requirement 10: Translation System Integration

**User Story:** As a user, I want my language preference to apply system-wide translations, so that all interface elements display in my selected language.

#### Acceptance Criteria

1. WHEN a user changes their language preference to Chinese, THE System SHALL load translations from the translation table where lang='zh'
2. WHEN a user changes their language preference to Bahasa Melayu, THE System SHALL load translations from the translation table where lang='ms'
3. WHEN a user changes their language preference to English, THE System SHALL load translations from the translation table where lang='en'
4. WHEN translations are loaded, THE System SHALL apply them to the sidebar navigation labels
5. WHEN translations are loaded, THE System SHALL apply them to page titles and headings
6. WHEN translations are loaded, THE System SHALL apply them to form labels and buttons
7. WHEN translations are loaded, THE System SHALL apply them to validation messages and system notifications
8. WHEN a translation key is not found for the selected language, THE System SHALL fall back to the English translation
9. WHEN a user logs in, THE System SHALL automatically load their saved language preference and apply translations
10. WHEN the System applies translations, THE System SHALL use the same translation mechanism as the system-wide localization settings (/pages/general/localization)

### Requirement 11: Access Control and User Category Integration

**User Story:** As a system administrator, I want all user categories to access Profile and Settings, so that every user can manage their personal information regardless of organizational role.

#### Acceptance Criteria

1. WHEN a Residen user accesses Profile and Settings, THE System SHALL allow full access to all features
2. WHEN an Agency user accesses Profile and Settings, THE System SHALL allow full access to all features
3. WHEN a Parliament user accesses Profile and Settings, THE System SHALL allow full access to all features
4. WHEN a DUN user accesses Profile and Settings, THE System SHALL allow full access to all features
5. WHEN a Contractor user accesses Profile and Settings, THE System SHALL allow full access to all features
6. WHEN a user views their Settings page, THE System SHALL display their user category badge based on their organizational assignment
7. WHEN a user attempts to change their username or user category, THE System SHALL prevent modification and indicate these fields are managed by administrators
