# Implementation Plan: User Profile and Settings Management

## Overview

This implementation plan focuses on integrating the user profile localization settings with the system-wide translation system. The core profile and settings functionality is already implemented, but the language preference needs to be connected to the translation table and applied system-wide to the UI, including the sidebar navigation.

## Tasks

- [x] 1. Create ApplyUserLocale Middleware
  - Create new middleware file `app/Http/Middleware/ApplyUserLocale.php`
  - Implement handle method to load user-specific locale from IntegrationSetting
  - Apply locale to application using `app()->setLocale()`
  - Store locale in session for persistence across requests
  - _Requirements: 4.12, 10.9_

- [x] 2. Register ApplyUserLocale Middleware
  - Register middleware in `app/Http/Kernel.php` in the web middleware group
  - Ensure middleware runs after authentication middleware
  - Test middleware loads user locale on each request
  - _Requirements: 4.12, 10.9_

- [ ] 3. Update Sidebar to Use Translation System
  - Modify `resources/views/components/layout/sidebar.blade.php` to use TranslationHelper
  - Replace hardcoded navigation labels with `TranslationHelper::trans()` calls
  - Add translation keys for all sidebar menu items (Overview, Pre-Project, Project, etc.)
  - Test sidebar displays correct language based on user preference
  - _Requirements: 10.4_

- [ ] 4. Update Page Titles to Use Translation System
  - Modify page views to use TranslationHelper for page titles
  - Replace hardcoded titles with translation keys
  - Add translation keys for all page titles
  - Test page titles display in user's selected language
  - _Requirements: 10.5_

- [ ] 5. Update Form Labels to Use Translation System
  - Modify profile and settings forms to use TranslationHelper for labels
  - Replace hardcoded labels with translation keys
  - Add translation keys for all form fields (Full Name, Email, Password, etc.)
  - Test form labels display in user's selected language
  - _Requirements: 10.6_

- [ ] 6. Update Validation Messages to Use Translation System
  - Modify validation rules to use translated messages
  - Create custom validation message translations
  - Add translation keys for all validation messages
  - Test validation messages display in user's selected language
  - _Requirements: 10.7_

- [x] 7. Add Translation Fallback Logic
  - Update TranslationHelper to implement fallback to English
  - If translation not found in selected language, try English
  - If English translation not found, return the key itself
  - Log missing translations for admin review
  - Test fallback works correctly for missing translations
  - _Requirements: 10.8_

- [ ] 8. Seed Translation Data for Profile and Settings
  - Create or update TranslationSeeder with profile and settings translations
  - Add English translations for all keys
  - Add Bahasa Melayu translations for all keys
  - Add Chinese translations for all keys
  - Run seeder to populate translation table
  - _Requirements: 10.1, 10.2, 10.3_

- [x] 9. Update Settings Controller to Apply Locale Immediately
  - Modify `settingsUpdate()` method to set session locale after saving
  - Call `app()->setLocale()` with new locale value
  - Store locale in session for immediate effect
  - Test locale change applies immediately without logout/login
  - _Requirements: 4.12_

- [x] 10. Checkpoint - Test Translation Integration
  - Test user can change language in Settings
  - Test sidebar navigation displays in selected language
  - Test page titles display in selected language
  - Test form labels display in selected language
  - Test validation messages display in selected language
  - Test fallback to English for missing translations
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 11. Write property test for username immutability
  - **Property 1: Profile Update Preserves Username Immutability**
  - **Validates: Requirements 1.6**
  - Generate random user with random username
  - Generate random profile update data
  - Update profile
  - Assert username remains unchanged

- [ ]* 12. Write property test for password verification
  - **Property 2: Password Change Requires Current Password Verification**
  - **Validates: Requirements 2.1, 2.2**
  - Generate random user with random password
  - Generate random incorrect current password
  - Attempt password change
  - Assert password change is rejected

- [ ]* 13. Write property test for password hashing
  - **Property 3: Password Hashing Consistency**
  - **Validates: Requirements 2.6**
  - Generate random user
  - Generate random new password
  - Change password
  - Assert stored password is bcrypt hash
  - Assert hash verifies against plaintext password

- [ ]* 14. Write property test for email uniqueness
  - **Property 4: Email Uniqueness Across Users**
  - **Validates: Requirements 1.3**
  - Generate two random users with different emails
  - Attempt to update user A's email to user B's email
  - Assert update is rejected

- [ ]* 15. Write property test for settings isolation
  - **Property 5: User Settings Isolation**
  - **Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5**
  - Generate two random users
  - Set random settings for user A
  - Set different random settings for user B
  - Assert user A's settings match what was set for user A
  - Assert user B's settings match what was set for user B

- [ ]* 16. Write property test for type prefix consistency
  - **Property 6: Settings Storage Type Prefix Consistency**
  - **Validates: Requirements 8.1, 8.2**
  - Generate random user
  - Set random setting
  - Query database directly
  - Assert type field equals 'user_{id}'

- [ ]* 17. Write property test for locale application
  - **Property 7: Locale Application After Settings Update**
  - **Validates: Requirements 4.12, 10.9**
  - Generate random user
  - Set random locale ('en', 'ms', or 'zh')
  - Make subsequent request as that user
  - Assert app locale matches user's selected locale

- [ ]* 18. Write property test for translation fallback
  - **Property 8: Translation Fallback to English**
  - **Validates: Requirements 10.8**
  - Generate random translation key that doesn't exist in Chinese
  - Set user locale to Chinese
  - Request translation
  - Assert returns English translation or key

- [ ]* 19. Write property test for boolean storage
  - **Property 9: Notification Preference Boolean Storage**
  - **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.7**
  - Generate random notification preference states (checked/unchecked)
  - Save settings
  - Query database directly
  - Assert all notification values are either 0 or 1

- [ ]* 20. Write property test for input preservation
  - **Property 10: Form Validation Preserves User Input**
  - **Validates: Requirements 1.8, 9.2**
  - Generate random invalid profile data
  - Submit form
  - Assert form re-renders with same input values

- [ ]* 21. Write property test for category badge display
  - **Property 11: User Category Badge Display Consistency**
  - **Validates: Requirements 3.2, 11.6**
  - Generate random user with one category assignment
  - Render settings page
  - Assert exactly one category badge is displayed
  - Assert badge color matches category type

- [ ]* 22. Write property test for password confirmation
  - **Property 12: Password Confirmation Matching**
  - **Validates: Requirements 2.4, 2.5**
  - Generate random user
  - Generate random new password
  - Generate different random confirmation password
  - Attempt password change
  - Assert change is rejected

- [ ]* 23. Write property test for translation integration
  - **Property 13: Translation System Integration**
  - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7**
  - Generate random user with Chinese locale
  - Load sidebar navigation
  - Assert all navigation labels use Chinese translations from translation table

- [ ]* 24. Write unit tests for profile update
  - Test successful profile update with valid data
  - Test profile update with invalid email format
  - Test profile update with duplicate email
  - Test profile update with empty required fields
  - _Requirements: 1.2, 1.3, 1.7, 1.8_

- [ ]* 25. Write unit tests for password change
  - Test successful password change with correct current password
  - Test password change rejection with incorrect current password
  - Test password change with short new password (< 8 characters)
  - Test password change with mismatched confirmation
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

- [ ]* 26. Write unit tests for settings update
  - Test successful settings update with valid localization preferences
  - Test successful settings update with valid notification preferences
  - Test settings update with invalid locale value
  - Test settings update with invalid timezone value
  - _Requirements: 4.1, 4.4, 4.7, 4.9, 5.1, 5.5_

- [ ]* 27. Write integration test for end-to-end profile flow
  - User logs in
  - Navigates to Profile page
  - Updates full name and email
  - Submits form
  - Verifies success message
  - Verifies database updated
  - _Requirements: 1.1, 1.2, 1.3, 1.7_

- [ ]* 28. Write integration test for end-to-end settings flow
  - User logs in
  - Navigates to Settings page
  - Changes language to Chinese
  - Changes timezone to Asia/Singapore
  - Enables email notifications
  - Submits form
  - Verifies success message
  - Verifies settings saved to database
  - Verifies UI displays in Chinese
  - _Requirements: 4.1, 4.4, 4.11, 5.1, 5.5, 10.1, 10.4_

- [ ]* 29. Write integration test for end-to-end password change flow
  - User logs in
  - Navigates to Profile page
  - Enters current password
  - Enters new password and confirmation
  - Submits form
  - Verifies success message
  - Logs out
  - Logs in with new password
  - Verifies login successful
  - _Requirements: 2.1, 2.3, 2.4, 2.6, 2.7_

- [ ] 30. Final checkpoint - Comprehensive testing
  - Run all unit tests and verify they pass
  - Run all property tests and verify they pass
  - Run all integration tests and verify they pass
  - Test with all three languages (English, Bahasa Melayu, Chinese)
  - Test with all user categories (Residen, Agency, Parliament, DUN, Contractor)
  - Verify data isolation between users
  - Verify translation fallback works correctly
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- The core profile and settings functionality is already implemented
- Focus is on integrating user locale preferences with the translation system
- Translation keys must be added to the translation table for all three languages
- Middleware must be registered in the correct order (after authentication)
- Session storage ensures locale persists across requests
- Fallback to English prevents broken UI when translations are missing
