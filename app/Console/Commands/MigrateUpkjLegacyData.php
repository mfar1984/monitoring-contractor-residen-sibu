<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContractorCategory;
use App\Models\ContractorUpkjRecord;
use App\Models\UpkjClassification;
use Illuminate\Support\Facades\DB;

class MigrateUpkjLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upkj:migrate-legacy
                            {--dry-run : Run without making changes}
                            {--force : Force migration even if records exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy UPKJ data (upkj_class, upkj_head, upkj_subhead) to contractor_upkj_records table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Starting UPKJ legacy data migration...');
        $this->info('Dry run: ' . ($dryRun ? 'Yes' : 'No'));
        $this->info('Force: ' . ($force ? 'Yes' : 'No'));
        $this->newLine();

        // Query contractors with legacy UPKJ data
        $contractors = ContractorCategory::whereNotNull('upkj_class')
            ->orWhereNotNull('upkj_head')
            ->orWhereNotNull('upkj_subhead')
            ->get();

        if ($contractors->isEmpty()) {
            $this->info('No contractors with legacy UPKJ data found.');
            return 0;
        }

        $this->info("Found {$contractors->count()} contractors with legacy UPKJ data.");
        $this->newLine();

        $migratedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($contractors as $contractor) {
            try {
                // Check if contractor already has UPKJ records
                $existingRecords = ContractorUpkjRecord::where('contractor_category_id', $contractor->id)->count();
                
                if ($existingRecords > 0 && !$force) {
                    $this->warn("Skipping contractor ID {$contractor->id} ({$contractor->company_name}) - already has {$existingRecords} UPKJ record(s)");
                    $skippedCount++;
                    continue;
                }

                // Build classification array from legacy data
                $classifications = $this->buildClassificationsFromLegacy(
                    $contractor->upkj_class,
                    $contractor->upkj_head,
                    $contractor->upkj_subhead
                );

                if (empty($classifications)) {
                    $this->warn("Skipping contractor ID {$contractor->id} ({$contractor->company_name}) - no valid classification data");
                    $skippedCount++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("Would migrate contractor ID {$contractor->id} ({$contractor->company_name}):");
                    $this->line("  - Class: {$contractor->upkj_class}");
                    $this->line("  - Head: {$contractor->upkj_head}");
                    $this->line("  - Subhead: {$contractor->upkj_subhead}");
                    $this->line("  - Classifications: " . json_encode($classifications));
                    $migratedCount++;
                    continue;
                }

                // Create UPKJ record
                DB::beginTransaction();
                try {
                    // Delete existing records if force is enabled
                    if ($force && $existingRecords > 0) {
                        ContractorUpkjRecord::where('contractor_category_id', $contractor->id)->delete();
                        $this->info("Deleted {$existingRecords} existing record(s) for contractor ID {$contractor->id}");
                    }

                    // Determine category based on class
                    $category = $this->determineCategoryFromClass($contractor->upkj_class);

                    // Determine registration status based on expiry date
                    $registrationStatus = 'Valid';
                    $validityPeriod = null;
                    $bumputeraValidity = null;
                    
                    if ($contractor->upk_expiry_date) {
                        $expiryDate = \Carbon\Carbon::parse($contractor->upk_expiry_date);
                        $validityPeriod = $expiryDate->format('d/m/Y');
                        
                        // Bumiputera validity is same as expiry date
                        $bumputeraValidity = $validityPeriod;
                        
                        // Check if expired
                        if ($expiryDate->isPast()) {
                            $registrationStatus = 'Expired';
                        }
                    }

                    // Create new UPKJ record
                    ContractorUpkjRecord::create([
                        'contractor_category_id' => $contractor->id,
                        'category' => $category,
                        'registration_status' => $registrationStatus,
                        'validity_period' => $validityPeriod,
                        'bumiputera_status' => $contractor->bumiputera_status ?? 'No',
                        'bumiputera_validity' => $bumputeraValidity,
                        'certificate_no' => $contractor->upk_license_no,
                        'classifications' => $classifications,
                    ]);

                    DB::commit();
                    $this->info("✓ Migrated contractor ID {$contractor->id} ({$contractor->company_name})");
                    $migratedCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            } catch (\Exception $e) {
                $this->error("✗ Error migrating contractor ID {$contractor->id} ({$contractor->company_name}): {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info('Migration Summary:');
        $this->info("  - Total contractors found: {$contractors->count()}");
        $this->info("  - Migrated: {$migratedCount}");
        $this->info("  - Skipped: {$skippedCount}");
        $this->info("  - Errors: {$errorCount}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. No changes were made to the database.');
            $this->info('Run without --dry-run to perform the actual migration.');
        }

        return 0;
    }

    /**
     * Build classifications array from legacy data
     * Handles complex formats:
     * - Multiple classes: "E, F"
     * - Multiple heads with prefixes: "E(II),E(III), F(I), F(IV), F(VI)"
     * - Multi-line subheads with detailed breakdown
     */
    private function buildClassificationsFromLegacy($class, $head, $subhead): array
    {
        $classifications = [];

        // Parse classes (comma-separated)
        $classes = $this->parseClasses($class);
        
        // Parse heads with class prefixes
        $headsByClass = $this->parseHeads($head);
        
        // Parse subheads (multi-line format)
        $subheadsByClassAndHead = $this->parseSubheads($subhead);

        // Build classifications by matching class, head, and subhead
        foreach ($classes as $cls) {
            // Get heads for this class
            $headsForClass = $headsByClass[$cls] ?? [];
            
            if (empty($headsForClass)) {
                // No heads specified, skip this class
                continue;
            }

            foreach ($headsForClass as $headCode) {
                // Get subheads for this class and head
                $subheadsForHead = $subheadsByClassAndHead[$cls][$headCode] ?? [];
                
                if (empty($subheadsForHead)) {
                    // No subheads specified, get all subheads for this class and head
                    $classificationList = UpkjClassification::where('class', $cls)
                        ->where('head_code', $headCode)
                        ->where('status', 'Active')
                        ->get();

                    foreach ($classificationList as $classification) {
                        $classifications[] = $this->formatClassification($classification);
                    }
                } else {
                    // Match specific subheads
                    foreach ($subheadsForHead as $subheadCode) {
                        $classification = $this->findClassification($cls, $headCode, $subheadCode);
                        if ($classification) {
                            $classifications[] = $this->formatClassification($classification);
                        }
                    }
                }
            }
        }

        return $classifications;
    }

    /**
     * Parse classes from comma-separated string
     * Example: "E, F" → ["E", "F"]
     */
    private function parseClasses($classString): array
    {
        if (empty($classString)) {
            return [];
        }

        // Split by comma and trim whitespace
        $classes = array_map('trim', explode(',', $classString));
        
        // Remove empty values
        return array_filter($classes);
    }

    /**
     * Parse heads with class prefixes
     * Example: "E(II),E(III), F(I), F(IV), F(VI)" → ["E" => ["II", "III"], "F" => ["I", "IV", "VI"]]
     */
    private function parseHeads($headString): array
    {
        $headsByClass = [];

        if (empty($headString)) {
            return $headsByClass;
        }

        // Split by comma
        $headParts = array_map('trim', explode(',', $headString));

        foreach ($headParts as $part) {
            // Match pattern: CLASS(HEAD)
            // Example: E(II) → class=E, head=II
            if (preg_match('/^([A-Z])\\(([IVX]+)\\)$/', $part, $matches)) {
                $class = $matches[1];
                $head = $matches[2];
                
                if (!isset($headsByClass[$class])) {
                    $headsByClass[$class] = [];
                }
                
                $headsByClass[$class][] = $head;
            }
        }

        return $headsByClass;
    }

    /**
     * Parse subheads from multi-line format
     * Example:
     * E       II      1(a),1(b),1(c),2(a),2(b),5(a),5(b),5(c)
     * E       III     1(a),1(b),1(c),1(d),1(e),2(a),2(b),2(c),2(d),3(a),4(a),4(b),4(c)
     * F       I       1,2(a)(i),2(a)(ii),2(b)(i),2(b)(ii),2(c)(ii)
     * 
     * Returns: ["E" => ["II" => ["1(a)", "1(b)", ...], "III" => [...]], "F" => ["I" => [...]]]
     */
    private function parseSubheads($subheadString): array
    {
        $subheadsByClassAndHead = [];

        if (empty($subheadString)) {
            return $subheadsByClassAndHead;
        }

        // Split by newline
        $lines = explode("\n", $subheadString);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Match pattern: CLASS   HEAD    SUBHEADS
            // Example: E       II      1(a),1(b),1(c),2(a),2(b),5(a),5(b),5(c)
            if (preg_match('/^([A-Z])\\s+([IVX]+)\\s+(.+)$/', $line, $matches)) {
                $class = $matches[1];
                $head = $matches[2];
                $subheadsStr = $matches[3];

                // Parse subheads (comma-separated)
                $subheads = array_map('trim', explode(',', $subheadsStr));

                if (!isset($subheadsByClassAndHead[$class])) {
                    $subheadsByClassAndHead[$class] = [];
                }

                $subheadsByClassAndHead[$class][$head] = $subheads;
            }
        }

        return $subheadsByClassAndHead;
    }

    /**
     * Find classification by class, head, and subhead code
     * Handles complex subhead formats like "1(a)", "2(a)(i)", etc.
     */
    private function findClassification($class, $headCode, $subheadCode)
    {
        // Parse subhead code to extract components
        // Examples:
        // "1" → subhead_code=1, letter=null, roman=null
        // "1(a)" → subhead_code=1, letter=a, roman=null
        // "2(a)(i)" → subhead_code=2, letter=a, roman=i

        $subheadNum = null;
        $subheadLetter = null;
        $subheadRoman = null;

        // Pattern 1: Just number (e.g., "1")
        if (preg_match('/^(\\d+)$/', $subheadCode, $matches)) {
            $subheadNum = $matches[1];
        }
        // Pattern 2: Number with letter (e.g., "1(a)")
        elseif (preg_match('/^(\\d+)\\(([a-z])\\)$/', $subheadCode, $matches)) {
            $subheadNum = $matches[1];
            $subheadLetter = $matches[2];
        }
        // Pattern 3: Number with letter and roman (e.g., "2(a)(i)")
        elseif (preg_match('/^(\\d+)\\(([a-z])\\)\\(([ivx]+)\\)$/', $subheadCode, $matches)) {
            $subheadNum = $matches[1];
            $subheadLetter = $matches[2];
            $subheadRoman = $matches[3];
        }

        // Build query
        $query = UpkjClassification::where('class', $class)
            ->where('head_code', $headCode)
            ->where('status', 'Active');

        if ($subheadNum !== null) {
            $query->where('subhead_code', $subheadNum);
        }

        if ($subheadLetter !== null) {
            $query->where('subhead_letter', $subheadLetter);
        }

        if ($subheadRoman !== null) {
            $query->where('subhead_roman', $subheadRoman);
        }

        return $query->first();
    }

    /**
     * Format classification for storage
     */
    private function formatClassification($classification): array
    {
        return [
            'class' => $classification->class,
            'class_description' => $classification->class_description,
            'head_code' => $classification->head_code,
            'head_name' => $classification->head_name,
            'subhead_code' => $classification->subhead_code,
            'subhead_letter' => $classification->subhead_letter,
            'subhead_roman' => $classification->subhead_roman,
            'description' => $classification->description,
        ];
    }

    /**
     * Determine category from UPKJ class
     */
    private function determineCategoryFromClass($class): string
    {
        // Map UPKJ classes to categories
        // A, B, C, D, E, F, G = Works
        // H = Supplies & Services
        // J = Electrical
        // K = Mechanical
        
        if (in_array($class, ['A', 'B', 'C', 'D', 'E', 'F', 'G'])) {
            return 'Works';
        } elseif ($class === 'H') {
            return 'Supplies & Services';
        } elseif ($class === 'J') {
            return 'Electrical';
        } elseif ($class === 'K') {
            return 'Mechanical';
        }

        // Default to Works if unknown
        return 'Works';
    }
}
