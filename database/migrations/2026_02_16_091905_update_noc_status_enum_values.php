<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, change column type to VARCHAR to allow any value
        DB::statement("ALTER TABLE nocs MODIFY COLUMN status VARCHAR(50) DEFAULT 'Waiting for Approval 1'");
        
        // Update existing status values to new format
        DB::statement("UPDATE nocs SET status = 'Waiting for Approval 1' WHERE status = 'Draft'");
        DB::statement("UPDATE nocs SET status = 'Waiting for Approval 1' WHERE status = 'Pending First Approval'");
        DB::statement("UPDATE nocs SET status = 'Waiting for Approval 2' WHERE status = 'Pending Second Approval'");
        
        // Now change back to ENUM with new values
        DB::statement("ALTER TABLE nocs MODIFY COLUMN status ENUM('Waiting for Approval 1', 'Waiting for Approval 2', 'Approved', 'Rejected') DEFAULT 'Waiting for Approval 1'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        DB::statement("ALTER TABLE nocs MODIFY COLUMN status ENUM('Draft', 'Pending First Approval', 'Pending Second Approval', 'Approved', 'Rejected') DEFAULT 'Draft'");
        
        // Revert status values
        DB::statement("UPDATE nocs SET status = 'Draft' WHERE status = 'Waiting for Approval 1'");
        DB::statement("UPDATE nocs SET status = 'Pending First Approval' WHERE status = 'Waiting for Approval 1'");
        DB::statement("UPDATE nocs SET status = 'Pending Second Approval' WHERE status = 'Waiting for Approval 2'");
    }
};
