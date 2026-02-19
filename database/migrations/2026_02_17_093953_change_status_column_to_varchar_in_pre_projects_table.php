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
        // Change status column from ENUM to VARCHAR
        DB::statement("ALTER TABLE pre_projects MODIFY COLUMN status VARCHAR(255) DEFAULT 'Active'");
        
        // Update existing statuses
        DB::table('pre_projects')
            ->where('status', 'Waiting For EPU Approval')
            ->update(['status' => 'Waiting for Complete Form']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (optional - may lose data if new statuses exist)
        DB::statement("ALTER TABLE pre_projects MODIFY COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active'");
    }
};
