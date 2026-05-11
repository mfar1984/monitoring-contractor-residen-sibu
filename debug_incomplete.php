<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ContractorUpkjRecord;
use App\Models\UpkjClassification;

echo "=== Debugging Incomplete Classifications ===\n\n";

// Get first record with incomplete classifications
$records = ContractorUpkjRecord::all();

$foundIncomplete = false;

foreach ($records as $record) {
    $classifications = $record->classifications;
    
    if (!is_array($classifications)) {
        continue;
    }
    
    foreach ($classifications as $classification) {
        if (empty($classification['head_name']) || empty($classification['description'])) {
            if (!$foundIncomplete) {
                echo "Found incomplete classification in record ID: {$record->id}\n";
                echo "Contractor: {$record->contractor->name}\n";
                echo "Category: {$record->category}\n\n";
                
                echo "Incomplete classification:\n";
                print_r($classification);
                echo "\n";
                
                // Try to find it
                $class = $classification['class'];
                $headCode = $classification['head_code'];
                $subheadCode = $classification['subhead_code'];
                $subheadRoman = $classification['subhead_roman'] ?? null;
                $subheadLetter = $classification['subhead_letter'] ?? null;
                
                echo "Searching for:\n";
                echo "  Class: $class\n";
                echo "  Head Code: $headCode\n";
                echo "  Subhead Code: $subheadCode\n";
                echo "  Subhead Roman: " . ($subheadRoman ?: 'NULL') . "\n";
                echo "  Subhead Letter: " . ($subheadLetter ?: 'NULL') . "\n\n";
                
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
                    echo "✅ FOUND in master data:\n";
                    echo "  Head Name: {$upkjClass->head_name}\n";
                    echo "  Description: {$upkjClass->description}\n";
                    echo "  Subhead Roman: " . ($upkjClass->subhead_roman ?: 'NULL') . "\n";
                    echo "  Subhead Letter: " . ($upkjClass->subhead_letter ?: 'NULL') . "\n";
                } else {
                    echo "❌ NOT FOUND in master data\n\n";
                    
                    // Show what exists for this class-head-subhead
                    $similar = UpkjClassification::where('class', $class)
                        ->where('head_code', $headCode)
                        ->where('subhead_code', $subheadCode)
                        ->get();
                    
                    if ($similar->count() > 0) {
                        echo "Similar codes found:\n";
                        foreach ($similar as $s) {
                            echo "  - Roman: " . ($s->subhead_roman ?: 'NULL') . ", Letter: " . ($s->subhead_letter ?: 'NULL') . " => {$s->description}\n";
                        }
                    }
                }
                
                $foundIncomplete = true;
                break 2;
            }
        }
    }
}

if (!$foundIncomplete) {
    echo "No incomplete classifications found!\n";
}
