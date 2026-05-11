<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;
use App\Models\UpkjClassification;

echo "=== Re-parsing Classifications with Correct Regex ===\n\n";

// Get all UPKJ records
$records = ContractorUpkjRecord::all();

echo "Total UPKJ records: " . $records->count() . "\n\n";

$fixedCount = 0;
$alreadyCorrectCount = 0;

foreach ($records as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications) || empty($classifications)) {
        continue;
    }
    
    $needsUpdate = false;
    $updatedClassifications = [];
    
    foreach ($classifications as $classification) {
        $class = $classification['class'];
        $headCode = $classification['head_code'];
        $subheadCode = $classification['subhead_code'];
        $subheadRoman = $classification['subhead_roman'] ?? null;
        $subheadLetter = $classification['subhead_letter'] ?? null;
        
        // Check if letter contains roman numerals (wrong parsing)
        // Examples: letter="iia" should be roman="ii" + letter="a"
        if ($subheadLetter && preg_match('/^([ivx]+)([a-z]*)$/i', $subheadLetter, $matches)) {
            // Re-parse: letter contains roman numerals
            $correctRoman = $matches[1];
            $correctLetter = $matches[2] ?: null;
            
            echo "Re-parsing: $class-$headCode-$subheadCode (letter='$subheadLetter')\n";
            echo "  OLD: roman=" . ($subheadRoman ?: 'NULL') . ", letter=$subheadLetter\n";
            echo "  NEW: roman=$correctRoman, letter=" . ($correctLetter ?: 'NULL') . "\n";
            
            $subheadRoman = $correctRoman;
            $subheadLetter = $correctLetter;
            $needsUpdate = true;
        }
        
        // Now look up in master data with correct values
        $upkjClass = UpkjClassification::where('class', $class)
            ->where('head_code', $headCode)
            ->where('subhead_code', $subheadCode)
            ->where(function($q) use ($subheadRoman) {
                if ($subheadRoman) {
                    $q->where('subhead_roman', $subheadRoman);
                } else {
                    $q->whereNull('subhead_roman')
                      ->orWhere('subhead_roman', '');
                }
            })
            ->where(function($q) use ($subheadLetter) {
                if ($subheadLetter) {
                    $q->where('subhead_letter', $subheadLetter);
                } else {
                    $q->whereNull('subhead_letter')
                      ->orWhere('subhead_letter', '');
                }
            })
            ->first();
        
        if ($upkjClass) {
            // Found! Use complete data
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
            
            if ($needsUpdate) {
                echo "  ✅ FOUND and FIXED: {$upkjClass->description}\n\n";
            }
        } else {
            // Not found - keep as-is but with corrected roman/letter
            $updatedClassifications[] = [
                'class' => $class,
                'head_code' => $headCode,
                'head_name' => $classification['head_name'] ?? '',
                'description' => $classification['description'] ?? '',
                'subhead_code' => $subheadCode,
                'subhead_roman' => $subheadRoman,
                'subhead_letter' => $subheadLetter,
                'class_description' => $classification['class_description'] ?? ''
            ];
            
            if ($needsUpdate) {
                echo "  ❌ NOT FOUND (kept with corrected roman/letter)\n\n";
            }
        }
    }
    
    if ($needsUpdate) {
        // Update record
        $record->classifications = $updatedClassifications;
        $record->save();
        $fixedCount++;
    } else {
        $alreadyCorrectCount++;
    }
}

echo "\n=== Results ===\n\n";
echo "✅ Fixed: $fixedCount records\n";
echo "✅ Already correct: $alreadyCorrectCount records\n";
echo "\nTotal: " . ($fixedCount + $alreadyCorrectCount) . " records\n";

// Now count incomplete classifications
echo "\n=== Checking for remaining incomplete classifications ===\n\n";

$incompleteCount = 0;
$completeCount = 0;

foreach (ContractorUpkjRecord::all() as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications)) {
        continue;
    }
    
    $hasIncomplete = false;
    foreach ($classifications as $c) {
        if (empty($c['head_name']) || empty($c['description'])) {
            $hasIncomplete = true;
            break;
        }
    }
    
    if ($hasIncomplete) {
        $incompleteCount++;
    } else {
        $completeCount++;
    }
}

echo "Records with complete classifications: $completeCount\n";
echo "Records with incomplete classifications: $incompleteCount\n";

if ($incompleteCount == 0) {
    echo "\n🎉 SUCCESS! All classifications are now complete!\n";
}
