<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;
use Illuminate\Support\Facades\DB;

echo "=== Adding ALL Supplies & Services Classifications for Class D, E, F ===\n\n";

$classDescriptions = [
    'D' => 'Supplies & Services - Above 1,000,000 And Above',
    'E' => 'Supplies & Services - Above 200,000 And 2,000,000',
    'F' => 'Supplies & Services - 200,000 And Below',
];

// Get all Supplies & Services Heads from Class A (reference)
$suppliesHeads = UpkjClassification::where('category', 'Supplies & Services')
    ->where('class', 'A')
    ->distinct()
    ->pluck('head_code')
    ->sort()
    ->values();

echo "Supplies & Services Heads to copy: " . $suppliesHeads->implode(', ') . "\n\n";

$targetClasses = ['D', 'E', 'F'];
$totalAdded = 0;
$totalSkipped = 0;

DB::beginTransaction();

try {
    foreach ($targetClasses as $targetClass) {
        echo "=== Processing Class $targetClass ({$classDescriptions[$targetClass]}) ===\n\n";
        
        foreach ($suppliesHeads as $head) {
            // Check if this class-head combination already exists
            $existingCount = UpkjClassification::where('category', 'Supplies & Services')
                ->where('class', $targetClass)
                ->where('head_code', $head)
                ->count();
            
            if ($existingCount > 0) {
                echo "Head $head: Already exists ($existingCount classifications) - SKIPPED\n";
                $totalSkipped += $existingCount;
                continue;
            }
            
            // Get all classifications from Class A for this head
            $sourceClassifications = UpkjClassification::where('category', 'Supplies & Services')
                ->where('class', 'A')
                ->where('head_code', $head)
                ->orderBy('subhead_code')
                ->orderBy('subhead_roman')
                ->orderBy('subhead_letter')
                ->get();
            
            if ($sourceClassifications->count() == 0) {
                echo "Head $head: No source data found - SKIPPED\n";
                continue;
            }
            
            echo "Head $head: Copying {$sourceClassifications->count()} classifications...\n";
            
            $addedCount = 0;
            
            foreach ($sourceClassifications as $source) {
                // Create new classification
                UpkjClassification::create([
                    'category' => $source->category,
                    'class' => $targetClass,
                    'head_code' => $head,
                    'head_name' => $source->head_name,
                    'description' => $source->description,
                    'subhead_code' => $source->subhead_code,
                    'subhead_roman' => $source->subhead_roman,
                    'subhead_letter' => $source->subhead_letter,
                    'class_description' => $classDescriptions[$targetClass],
                    'status' => 'active',
                ]);
                
                $addedCount++;
            }
            
            echo "  ✅ Added $addedCount classifications for $targetClass-$head\n";
            $totalAdded += $addedCount;
        }
        
        echo "\n";
    }
    
    DB::commit();
    
    echo "\n=== SUMMARY ===\n\n";
    echo "✅ Successfully added $totalAdded new Supplies & Services classifications!\n";
    echo "⏭️  Skipped $totalSkipped existing classifications\n\n";
    
    echo "Breakdown by Head:\n";
    
    // Show breakdown
    $heads = [
        'I' => 'Civil Engineering Building Materials',
        'II' => 'Mechanical & Electrical Engineering Plant / Equipment',
        'III' => 'Water Supply Materials',
        'IV' => 'Office Machines / Equipment / Technical Supplies',
        'V' => 'Chemicals And Materials',
        'VI' => 'General Supply',
        'VII' => 'Charter Services',
        'VIII' => 'Books / Printing',
        'IX' => 'Miscellaneous',
        'X' => 'Information And Communication Technology',
        'XI' => 'Hospital Equipment And Supplies',
    ];
    
    foreach ($heads as $headCode => $headName) {
        $countD = UpkjClassification::where('category', 'Supplies & Services')
            ->where('class', 'D')
            ->where('head_code', $headCode)
            ->count();
        
        $countE = UpkjClassification::where('category', 'Supplies & Services')
            ->where('class', 'E')
            ->where('head_code', $headCode)
            ->count();
        
        $countF = UpkjClassification::where('category', 'Supplies & Services')
            ->where('class', 'F')
            ->where('head_code', $headCode)
            ->count();
        
        if ($countD > 0 || $countE > 0 || $countF > 0) {
            echo "\nHead $headCode: $headName\n";
            echo "  Class D: $countD classifications\n";
            echo "  Class E: $countE classifications\n";
            echo "  Class F: $countF classifications\n";
        }
    }
    
    echo "\n\n🎉 ALL Supplies & Services classifications for Class D, E, F are now complete!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes made.\n";
}
