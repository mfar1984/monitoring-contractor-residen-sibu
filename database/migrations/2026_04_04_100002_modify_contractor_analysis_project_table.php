<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the constraint if it exists, then recreate it
        try {
            Schema::table('contractor_analysis_project', function (Blueprint $table) {
                $table->dropUnique('unique_transfer_project');
            });
        } catch (\Exception $e) {
            // Constraint doesn't exist, continue
        }
        
        // Add unique constraint to ensure ONE project per transfer
        Schema::table('contractor_analysis_project', function (Blueprint $table) {
            $table->unique(
                ['contractor_analysis_transfer_id', 'project_id'],
                'unique_transfer_project'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractor_analysis_project', function (Blueprint $table) {
            $table->dropUnique('unique_transfer_project');
        });
    }
};
