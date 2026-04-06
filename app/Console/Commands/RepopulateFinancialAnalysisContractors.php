<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialAnalysisContractor;
use App\Models\ContractorCategory;

class RepopulateFinancialAnalysisContractors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial-analysis:repopulate-contractors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-populate contractor Class and UPKJ data for existing Financial Analysis records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to re-populate contractor data...');
        
        // Get all financial analysis contractors
        $contractors = FinancialAnalysisContractor::with('contractor.upkjRecords')->get();
        
        $this->info("Found {$contractors->count()} contractor records to update.");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($contractors as $analysisContractor) {
            if (!$analysisContractor->contractor) {
                $this->warn("Skipping contractor ID {$analysisContractor->id} - contractor not found");
                $skipped++;
                continue;
            }
            
            // Re-populate data from contractor
            $analysisContractor->populateFromContractor($analysisContractor->contractor);
            $analysisContractor->save();
            
            $this->line("Updated: {$analysisContractor->contractor_name} - Class: {$analysisContractor->contractor_class}, UPKJ: {$analysisContractor->upkj_classifications}");
            $updated++;
        }
        
        $this->info("\nCompleted!");
        $this->info("Updated: {$updated} records");
        $this->info("Skipped: {$skipped} records");
        
        return 0;
    }
}
