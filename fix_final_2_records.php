<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;
use App\Models\UpkjClassification;

echo "=== Fixing Final 2 Incomplete Records ===\n\n";

// Record 1093 - SYARIKAT TANAH SEBERAI - F-IV-3
echo "1. Fixing Record ID 1093 (SYARIKAT TANAH SEBERAI - F-IV-3):\n";

$record1093 = ContractorUpkjRecord::find(1093);

if ($record1093) {
    echo "   Contractor: {$record1093->contractor->company_name}\n";
    echo "   Category: {$record1093->category}\n";
    
    $classifications = $record1093->classifications;
    $updated = false;
    
    foreach ($classifications as &$c) {
        if ($c['class'] == 'F' && 
            $c['head_code'] == 'IV' && 
            $c['subhead_code'] == '3' &&
            empty($c['head_name'])) {
            
            // Look up F-IV-3 in master data
            $upkjClass = UpkjClassification::where('class', 'F')
                ->where('head_code', 'IV')
                ->where('subhead_code', '3')
                ->whereNull('subhead_roman')
                ->whereNull('subhead_letter')
                ->first();
            
            if ($upkjClass) {
                echo "   ✅ Found F-IV-3 in master data: {$upkjClass->description}\n";
                
                // Update classification
                $c['head_name'] = $upkjClass->head_name;
                $c['description'] = $upkjClass->description;
                $c['class_description'] = $upkjClass->class_description;
                
                $updated = true;
            } else {
                echo "   ❌ F-IV-3 still not found in master data!\n";
            }
        }
    }
    
    if ($updated) {
        $record1093->classifications = $classifications;
        $record1093->save();
        echo "   ✅ Record 1093 FIXED!\n\n";
    }
} else {
    echo "   ❌ Record 1093 not found!\n\n";
}

// Record 1094 - TATAI MAS - EX-I-1
echo "2. Checking Record ID 1094 (TATAI MAS - EX-I-1):\n";

$record1094 = ContractorUpkjRecord::find(1094);

if ($record1094) {
    echo "   Contractor: {$record1094->contractor->company_name}\n";
    echo "   Category: {$record1094->category}\n";
    
    $classifications = $record1094->classifications;
    
    foreach ($classifications as $c) {
        if ($c['class'] == 'EX') {
            echo "   ❌ Found invalid code: EX-{$c['head_code']}-{$c['subhead_code']}\n";
            echo "   Class 'EX' is NOT a valid UPKJ class!\n";
            echo "   This is a data entry error and should be removed manually.\n\n";
        }
    }
} else {
    echo "   ❌ Record 1094 not found!\n\n";
}

echo "=== Final Check ===\n\n";

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
echo "Records with incomplete classifications: $incompleteCount\n\n";

if ($incompleteCount == 1) {
    echo "✅ Only 1 incomplete record remaining (TATAI MAS with invalid EX-I-1 code)\n";
    echo "This is a data entry error and cannot be auto-fixed.\n";
} elseif ($incompleteCount == 0) {
    echo "🎉 ALL RECORDS COMPLETE!\n";
}
