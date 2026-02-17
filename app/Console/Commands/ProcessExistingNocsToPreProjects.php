<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noc;
use App\Services\NocToPreProjectService;

class ProcessExistingNocsToPreProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noc:process-to-preprojects {--noc-id=* : Specific NOC IDs to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process existing NOCs and create pre-projects from their changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nocIds = $this->option('noc-id');
        
        // Get NOCs to process
        if (!empty($nocIds)) {
            $nocs = Noc::whereIn('id', $nocIds)->get();
            $this->info("Processing " . count($nocs) . " specific NOC(s)...");
        } else {
            $nocs = Noc::all();
            $this->info("Processing all " . count($nocs) . " NOC(s)...");
        }
        
        if ($nocs->isEmpty()) {
            $this->error('No NOCs found to process.');
            return 1;
        }
        
        $nocService = new NocToPreProjectService();
        $totalCreated = 0;
        
        foreach ($nocs as $noc) {
            $this->info("Processing NOC #{$noc->id} ({$noc->noc_number})...");
            
            try {
                $createdPreProjects = $nocService->processNocSubmission($noc);
                $count = count($createdPreProjects);
                $totalCreated += $count;
                
                if ($count > 0) {
                    $this->info("  ✓ Created {$count} pre-project(s) from NOC #{$noc->id}");
                } else {
                    $this->warn("  ⚠ No pre-projects created from NOC #{$noc->id} (no changes detected)");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to process NOC #{$noc->id}: " . $e->getMessage());
            }
        }
        
        $this->info("\n" . str_repeat('=', 50));
        $this->info("Total pre-projects created: {$totalCreated}");
        $this->info(str_repeat('=', 50));
        
        return 0;
    }
}
