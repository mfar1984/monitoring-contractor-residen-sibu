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
        // Check if column still needs to be renamed
        if (Schema::hasColumn('noc_project', 'pre_project_id')) {
            Schema::table('noc_project', function (Blueprint $table) {
                // Drop foreign key constraint if it exists
                try {
                    $table->dropForeign('noc_pre_project_pre_project_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key might already be dropped, continue
                }
                
                // Rename pre_project_id column to project_id
                $table->renameColumn('pre_project_id', 'project_id');
            });
        }
        
        // NOTE: Foreign key constraint will be added in a later migration
        // after data mapping is complete (Task 5.3)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('noc_project', function (Blueprint $table) {
            // Rename project_id column back to pre_project_id
            $table->renameColumn('project_id', 'pre_project_id');
        });
        
        Schema::table('noc_project', function (Blueprint $table) {
            // Add foreign key constraint back on pre_project_id
            $table->foreign('pre_project_id')
                  ->references('id')
                  ->on('pre_projects')
                  ->onDelete('cascade');
        });
    }
};
