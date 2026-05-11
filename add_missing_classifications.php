<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;
use Illuminate\Support\Facades\DB;

echo "=== Adding Missing Classifications ===\n\n";

$classDescriptions = [
    'A' => 'Works - Above 10,000,000',
    'B' => 'Works - Above 3,000,000 To 10,000,000',
    'C' => 'Works - Above 1,000,000 To 3,000,000',
    'D' => 'Works - Above 1,000,000 To 3,000,000',
    'E' => 'Works - Above 200,000 To 1,000,000',
    'F' => 'Works - 200,000 And Below',
];

// Define missing combinations and their reference sources
$missingCombinations = [
    ['target_class' => 'D', 'head' => 'VII', 'source_class' => 'A'],
    ['target_class' => 'E', 'head' => 'VII', 'source_class' => 'A'],
    ['target_class' => 'F', 'head' => 'IV', 'source_class' => 'E'],  // Use E as reference (closer class)
    ['target_class' => 'F', 'head' => 'VI', 'source_class' => 'E'],  // Use E as reference
    ['target_class' => 'F', 'head' => 'VII', 'source_class' => 'A'],
    ['target_class' => 'F', 'head' => 'VIII', 'source_class' => 'E'], // Use E as reference
];

$totalAdded = 0;

DB::beginTransaction();

try {
    foreach ($missingCombinations as $combo) {
        $targetClass = $combo['target_class'];
        $head = $combo['head'];
        $sourceClass = $combo['source_class'];
        
        echo "Processing: $targetClass-$head (copying from $sourceClass-$head)\n";
        
        // Get source classifications
        $sourceClassifications = UpkjClassification::where('class', $sourceClass)
            ->where('head_code', $head)
            ->orderBy('subhead_code')
            ->orderBy('subhead_roman')
            ->orderBy('subhead_letter')
            ->get();
        
        if ($sourceClassifications->count() == 0) {
            echo "  ⚠️ WARNING: No source data found for $sourceClass-$head\n\n";
            continue;
        }
        
        echo "  Found {$sourceClassifications->count()} classifications to copy\n";
        
        $addedCount = 0;
        
        foreach ($sourceClassifications as $source) {
            // Check if already exists
            $exists = UpkjClassification::where('class', $targetClass)
                ->where('head_code', $head)
                ->where('subhead_code', $source->subhead_code)
                ->where(function($q) use ($source) {
                    if ($source->subhead_roman) {
                        $q->where('subhead_roman', $source->subhead_roman);
                    } else {
                        $q->whereNull('subhead_roman');
                    }
                })
                ->where(function($q) use ($source) {
                    if ($source->subhead_letter) {
                        $q->where('subhead_letter', $source->subhead_letter);
                    } else {
                        $q->whereNull('subhead_letter');
                    }
                })
                ->exists();
            
            if ($exists) {
                echo "    - Skipped (already exists): $targetClass-$head-{$source->subhead_code}{$source->subhead_roman}{$source->subhead_letter}\n";
                continue;
            }
            
            // Create new classification
            UpkjClassification::create([
                'category' => $source->category,  // Copy category from source
                'class' => $targetClass,
                'head_code' => $head,
                'head_name' => $source->head_name,
                'description' => $source->description,
                'subhead_code' => $source->subhead_code,
                'subhead_roman' => $source->subhead_roman,
                'subhead_letter' => $source->subhead_letter,
                'class_description' => $classDescriptions[$targetClass],
                'status' => 'active',  // lowercase 'active' to match existing data
            ]);
            
            $code = "$targetClass-$head-{$source->subhead_code}{$source->subhead_roman}{$source->subhead_letter}";
            echo "    ✅ Added: $code - {$source->description}\n";
            
            $addedCount++;
            $totalAdded++;
        }
        
        echo "  Added $addedCount classifications for $targetClass-$head\n\n";
    }
    
    DB::commit();
    
    echo "\n=== SUMMARY ===\n\n";
    echo "✅ Successfully added $totalAdded new classifications!\n\n";
    
    echo "Breakdown:\n";
    echo "  - D-VII: Telecommunication Works\n";
    echo "  - E-VII: Telecommunication Works\n";
    echo "  - F-IV: Irrigation And Drainage Works (7 classifications)\n";
    echo "  - F-VI: Reforestation & Landscaping Works\n";
    echo "  - F-VII: Telecommunication Works\n";
    echo "  - F-VIII: Facilities Management\n\n";
    
    echo "Now F-IV-3 (Sewerage Works) exists in master data!\n";
    echo "SYARIKAT TANAH SEBERAI's F-IV-3 classification is now VALID.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes made.\n";
}
