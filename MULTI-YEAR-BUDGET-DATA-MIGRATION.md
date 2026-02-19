# Multi-Year Budget Data Migration

## Overview

This document describes the data migration created for Task 10 of the multi-year-budget-allocation spec. The migration transfers existing single-budget data from `parliaments.budget` and `duns.budget` columns to the new multi-year budget tables (`parliament_budgets` and `dun_budgets`).

## Migration File

**File**: `database/migrations/2026_02_19_120000_migrate_budget_data_to_multi_year_tables.php`

## What It Does

### Forward Migration (up)

1. **Parliament Budget Migration**:
   - Checks if `budget` column exists in `parliaments` table
   - Reads all non-null, positive budget values from `parliaments.budget`
   - Creates entries in `parliament_budgets` table for current year
   - Drops the `budget` column from `parliaments` table
   - Logs errors for individual records but continues processing

2. **DUN Budget Migration**:
   - Checks if `budget` column exists in `duns` table
   - Reads all non-null, positive budget values from `duns.budget`
   - Creates entries in `dun_budgets` table for current year
   - Drops the `budget` column from `duns` table
   - Logs errors for individual records but continues processing

### Rollback Migration (down)

1. **Parliament Budget Restoration**:
   - Adds `budget` column back to `parliaments` table (default 0)
   - Copies current year budget values from `parliament_budgets` back to `parliaments.budget`
   - Logs errors but continues processing

2. **DUN Budget Restoration**:
   - Adds `budget` column back to `duns` table (default 0)
   - Copies current year budget values from `dun_budgets` back to `duns.budget`
   - Logs errors but continues processing

## Key Features

### Safety Mechanisms

1. **Column Existence Check**: Verifies budget columns exist before attempting migration
2. **Non-Null Filter**: Only migrates records with non-null budget values
3. **Positive Values Only**: Only migrates budget values greater than 0
4. **Error Handling**: Catches exceptions for individual records and logs them
5. **Graceful Continuation**: Continues processing even if individual records fail

### Data Integrity

1. **Current Year Assignment**: All migrated budgets are assigned to the current calendar year
2. **Timestamps**: Sets `created_at` and `updated_at` for all new budget entries
3. **Decimal Precision**: Maintains DECIMAL(15,2) precision throughout migration
4. **Unique Constraints**: Respects unique constraints on (parliament_id, year) and (dun_id, year)

### Logging

- Warnings logged for individual record failures
- Errors logged for migration process failures
- Includes entity ID in log messages for debugging

## Running the Migration

### Forward Migration

```bash
php artisan migrate
```

This will:
- Create budget entries for current year from existing data
- Remove old budget columns
- Log any errors to `storage/logs/laravel.log`

### Rollback

```bash
php artisan migrate:rollback --step=1
```

This will:
- Restore budget columns to original tables
- Copy current year budget data back
- Keep multi-year budget tables intact (they were created in earlier migrations)

## Important Notes

### Before Running

1. **Backup Database**: Always backup your database before running data migrations
2. **Check Logs**: Review logs after migration to identify any failed records
3. **Verify Data**: Query the new budget tables to ensure data was migrated correctly

### After Running

1. **Verify Migration**:
   ```sql
   -- Check parliament budgets
   SELECT p.name, pb.year, pb.budget 
   FROM parliaments p 
   LEFT JOIN parliament_budgets pb ON p.id = pb.parliament_id;
   
   -- Check dun budgets
   SELECT d.name, db.year, db.budget 
   FROM duns d 
   LEFT JOIN dun_budgets db ON d.id = db.dun_id;
   ```

2. **Check for Errors**:
   ```bash
   tail -f storage/logs/laravel.log | grep "budget migration"
   ```

3. **Verify Column Removal**:
   ```sql
   DESCRIBE parliaments;
   DESCRIBE duns;
   ```
   The `budget` column should no longer exist.

## Edge Cases Handled

1. **No Budget Data**: If no budget values exist, migration completes successfully
2. **Zero Budgets**: Budget values of 0 are not migrated (only positive values)
3. **Null Budgets**: Null budget values are skipped
4. **Missing Columns**: If budget columns don't exist, migration skips that table
5. **Duplicate Years**: Won't create duplicates due to unique constraints
6. **Foreign Key Violations**: Logged and skipped for individual records

## Rollback Considerations

### What Gets Restored

- Budget columns are added back to original tables
- Current year budget values are copied back
- Default value of 0 for records without budget entries

### What Doesn't Get Restored

- Multi-year budget tables remain (they were created in earlier migrations)
- Budget entries for years other than current year remain in budget tables
- Historical budget data for multiple years is preserved

## Testing Recommendations

### Before Production

1. **Test on Development Database**:
   ```bash
   # Run migration
   php artisan migrate
   
   # Verify data
   php artisan tinker
   >>> Parliament::with('budgets')->first()
   >>> Dun::with('budgets')->first()
   
   # Test rollback
   php artisan migrate:rollback --step=1
   
   # Verify restoration
   >>> Parliament::first()->budget
   >>> Dun::first()->budget
   ```

2. **Test with Sample Data**:
   - Create test parliaments with various budget values (0, null, positive)
   - Run migration and verify correct handling
   - Test rollback and verify data restoration

3. **Test Error Scenarios**:
   - Simulate foreign key violations
   - Test with missing columns
   - Verify error logging

## Requirements Validated

This migration satisfies the following requirements from the spec:

- **Requirement 6.1**: Creates budget entries for current year using existing parliament budget values
- **Requirement 6.2**: Creates budget entries for current year using existing dun budget values
- **Requirement 6.3**: Removes budget column from parliaments table
- **Requirement 6.4**: Removes budget column from duns table
- **Requirement 6.5**: Rollback restores budget column to parliaments table
- **Requirement 6.6**: Rollback restores budget column to duns table

## Migration Order

This migration should run AFTER:
1. `2026_02_19_105721_create_parliament_budgets_table.php`
2. `2026_02_19_105847_create_dun_budgets_table.php`

The timestamp `2026_02_19_120000` ensures it runs after the budget tables are created.

## Support

If you encounter issues during migration:

1. Check `storage/logs/laravel.log` for detailed error messages
2. Verify database structure matches expected schema
3. Ensure budget tables exist before running migration
4. Contact system administrator if data inconsistencies are found
