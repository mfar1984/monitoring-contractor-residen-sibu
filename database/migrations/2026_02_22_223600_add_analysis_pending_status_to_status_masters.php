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
        // Check if status already exists before inserting
        $exists = DB::table('status_master')
            ->where('code', 'ANALYSIS_PENDING')
            ->exists();
        
        if (!$exists) {
            DB::table('status_master')->insert([
                'name' => 'Analysis Pending',
                'code' => 'ANALYSIS_PENDING',
                'description' => 'Project is awaiting contractor analysis',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('status_master')
            ->where('code', 'ANALYSIS_PENDING')
            ->delete();
    }
};
