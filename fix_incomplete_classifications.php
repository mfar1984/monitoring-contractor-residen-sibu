<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;
use App\Models\UpkjClassification;

echo "=== Fixing Incomplete Classifications ===\n\n";

// Get all UPKJ records
$records = ContractorUpkjRecord::all();

echo "Total UPKJ records: " . $records->count() . "\n\n";

$fixedCount = 0;
$alreadyCompleteCount = 0;
$stillIncompleteCount = 0;

foreach ($records as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications) || empty($classifications)) {
        continue;
    }
    
    $hasIncomplete = false;
    $updatedClassifications = [];
    
    foreach ($classifications as $classification) {
        // Check if incomplete (missing head_name or description)
        if (empty($classification['head_name']) || empty($classification['description'])) {
            $hasIncomplete = true;
            
            // Try to find in master data
            $class = $classification['class'];
            $headCode = $classification['head_code'];
            $subheadCode = $classification['subhead_code'];
            $subheadRoman = $classification['subhead_roman'] ?? null;
            $subheadLetter = $classification['subhead_letter'] ?? null;
            
            $upkjClass = UpkjClassification::where('class', $class)
                ->where('head_code', $headCode)
                ->where('subhead_code', $subheadCode)
                ->where(function($q) use ($subheadRoman) {
                    if ($subheadRoman) {
                        $q->where('subhead_roman', $subheadRoman);
                    } else {
                        $q->whereNull('subhead_roman');
                    }
                })
                ->where(function($q) use ($subheadLetter) {
                    if ($subheadLetter) {
                        $q->where('subhead_letter', $subheadLetter);
                    } else {
                        $q->whereNull('subhead_letter');
                    }
                })
                ->first();
            
            if ($upkjClass) {
                // Found! Update with complete data
                $updatedClassifications[] = [
                    'class' => $upkjClass->class,
                    'head_code' => $upkjClass->head_code,
                    'head_name' => $upkjClass->head_name,
                    'description' => $upkjClass->description,
                    'subhead_code' => $upkjClass->subhead_code,
                    'subhead_roman' => $upkjClass->subhead_roman,
                    'subhead_letter' => $upkjClass->subhead_letter,
                    'class_description' => $upkjClass->class_description
                ];
            } else {
                // Still not found - keep as-is
                $updatedClassifications[] = $classification;
            }
        } else {
            // Already complete
            $updatedClassifications[] = $classification;
        }
    }
    
    if ($hasIncomplete) {
        // Update record
        $record->classifications = $updatedClassifications;
        $record->save();
        
        // Check if all are now complete
        $stillHasIncomplete = false;
        foreach ($updatedClassifications as $c) {
            if (empty($c['head_name']) || empty($c['description'])) {
                $stillHasIncomplete = true;
                break;
            }
        }
        
        if ($stillHasIncomplete) {
            $stillIncompleteCount++;
        } else {
            $fixedCount++;
        }
    } else {
        $alreadyCompleteCount++;
    }
}

echo "\n=== Results ===\n\n";
echo "✅ Fixed (now complete): $fixedCount records\n";
echo "✅ Already complete: $alreadyCompleteCount records\n";
echo "❌ Still incomplete: $stillIncompleteCount records\n";
echo "\nTotal: " . ($fixedCount + $alreadyCompleteCount + $stillIncompleteCount) . " records\n";

if ($stillIncompleteCount > 0) {
    echo "\n⚠️ Warning: $stillIncompleteCount records still have incomplete classifications.\n";
    echo "These classifications don't exist in the master data table.\n";
}
