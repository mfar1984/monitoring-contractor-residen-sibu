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
        // Map NOC data from pre_projects to projects
        // For each noc_project record, find the corresponding project via pre_project_id
        
        $nocProjects = DB::table('noc_project')->whereNotNull('project_id')->get();
        
        $mappedCount = 0;
        $unmappedCount = 0;
        $unmappedRecords = [];
        
        foreach ($nocProjects as $nocProject) {
            // Find the project that was transferred from this pre_project
            $project = DB::table('projects')
                ->where('pre_project_id', $nocProject->project_id)
                ->first();
            
            if ($project) {
                // Update the noc_project record to reference the project
                DB::table('noc_project')
                    ->where('id', $nocProject->id)
                    ->update(['project_id' => $project->id]);
                
                $mappedCount++;
            } else {
                // Log unmapped records for manual review
                $unmappedCount++;
                $unmappedRecords[] = [
                    'noc_project_id' => $nocProject->id,
                    'noc_id' => $nocProject->noc_id,
                    'old_pre_project_id' => $nocProject->project_id,
                ];
                
                \Log::warning('NOC project record could not be mapped to project', [
                    'noc_project_id' => $nocProject->id,
                    'noc_id' => $nocProject->noc_id,
                    'pre_project_id' => $nocProject->project_id,
                ]);
            }
        }
        
        // Log summary
        \Log::info('NOC data mapping completed', [
            'total_records' => $nocProjects->count(),
            'mapped' => $mappedCount,
            'unmapped' => $unmappedCount,
            'unmapped_records' => $unmappedRecords
        ]);
        
        // Output summary to console
        echo "\n";
        echo "NOC Data Mapping Summary:\n";
        echo "========================\n";
        echo "Total NOC Project Records: " . $nocProjects->count() . "\n";
        echo "Successfully Mapped: " . $mappedCount . "\n";
        echo "Unmapped Records: " . $unmappedCount . "\n";
        
        if ($unmappedCount > 0) {
            echo "\nUnmapped Records (check logs for details):\n";
            foreach ($unmappedRecords as $record) {
                echo "  - NOC Project ID {$record['noc_project_id']}: NOC ID {$record['noc_id']}, Pre-Project ID {$record['old_pre_project_id']}\n";
            }
            
            // Set unmapped records to NULL so foreign key constraint can be added
            echo "\nSetting unmapped records to NULL...\n";
            foreach ($unmappedRecords as $record) {
                DB::table('noc_project')
                    ->where('id', $record['noc_project_id'])
                    ->update(['project_id' => null]);
            }
            echo "Unmapped records set to NULL.\n";
        }
        echo "\n";
        
        // Now add the foreign key constraint
        Schema::table('noc_project', function (Blueprint $table) {
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
        });
        
        echo "Foreign key constraint added successfully.\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('noc_project', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
        
        // Note: We cannot reverse the data mapping as we don't store the original pre_project_id
        // This is acceptable as the down() migration is rarely used in production
        echo "\n";
        echo "Foreign key constraint dropped.\n";
        echo "Note: Data mapping cannot be reversed automatically.\n";
        echo "\n";
    }
};
