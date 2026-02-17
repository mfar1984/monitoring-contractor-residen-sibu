<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PreProject;
use App\Services\ProjectTransferService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all approved pre-projects
        $approvedPreProjects = PreProject::where('status', 'Approved')->get();
        
        $transferService = new ProjectTransferService();
        $successCount = 0;
        $failureCount = 0;
        $failures = [];
        
        foreach ($approvedPreProjects as $preProject) {
            try {
                // Use ProjectTransferService to transfer each pre-project
                $transferService->transfer($preProject);
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                $failures[] = [
                    'pre_project_id' => $preProject->id,
                    'pre_project_name' => $preProject->name,
                    'error' => $e->getMessage()
                ];
                
                // Log the error for manual review
                \Log::error('Failed to transfer pre-project', [
                    'pre_project_id' => $preProject->id,
                    'pre_project_name' => $preProject->name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Log summary
        \Log::info('Pre-project transfer completed', [
            'total_approved' => $approvedPreProjects->count(),
            'successful_transfers' => $successCount,
            'failed_transfers' => $failureCount,
            'failures' => $failures
        ]);
        
        // Output summary to console
        echo "\n";
        echo "Pre-Project Transfer Summary:\n";
        echo "============================\n";
        echo "Total Approved Pre-Projects: " . $approvedPreProjects->count() . "\n";
        echo "Successfully Transferred: " . $successCount . "\n";
        echo "Failed Transfers: " . $failureCount . "\n";
        
        if ($failureCount > 0) {
            echo "\nFailed Transfers (check logs for details):\n";
            foreach ($failures as $failure) {
                echo "  - Pre-Project ID {$failure['pre_project_id']}: {$failure['pre_project_name']}\n";
                echo "    Error: {$failure['error']}\n";
            }
        }
        echo "\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // WARNING: This will delete all transferred projects
        // Only use this if you need to rollback the migration
        
        // Get all projects that were transferred from pre-projects
        $transferredProjects = \App\Models\Project::whereNotNull('pre_project_id')->get();
        
        echo "\n";
        echo "Rolling back project transfers...\n";
        echo "Deleting " . $transferredProjects->count() . " transferred projects\n";
        
        // Delete all transferred projects
        \App\Models\Project::whereNotNull('pre_project_id')->delete();
        
        echo "Rollback completed\n";
        echo "\n";
    }
};
