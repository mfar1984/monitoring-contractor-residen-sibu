<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration will DELETE ALL DATA from:
     * - projects table
     * - nocs table
     * - noc_project pivot table
     * - pre_projects table
     * 
     * Master data and users will NOT be affected.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables (delete all data but keep structure)
        DB::table('noc_project')->truncate();
        DB::table('nocs')->truncate();
        DB::table('projects')->truncate();
        DB::table('pre_projects')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This cannot restore deleted data.
     * This is here for migration rollback compatibility only.
     */
    public function down(): void
    {
        // Cannot restore deleted data
        // This down method is intentionally empty
    }
};
