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
        // Modify the status enum to include new approval statuses
        DB::statement("ALTER TABLE pre_projects MODIFY COLUMN status ENUM('Active', 'Waiting for Approval', 'Waiting for EPU Approval', 'Approved', 'NOC') DEFAULT 'Waiting for Approval'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pre_projects MODIFY COLUMN status ENUM('Active', 'Approved', 'NOC') DEFAULT 'Active'");
    }
};
