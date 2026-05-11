<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check Electrical category for all classes
$classes = ['A', 'B', 'C', 'D', 'E', 'F'];

echo "=== ELECTRICAL CATEGORY VERIFICATION ===\n\n";

foreach ($classes as $class) {
    echo "CLASS $class:\n";
    
    // Get all Electrical heads for this class
    $heads = DB::table('upkj_classifications')
        ->where('class', $class)
        ->where('category', 'Electrical')
        ->select('head_code', 'head_name')
        ->distinct()
        ->orderBy('head_code')
        ->get();
    
    if ($heads->isEmpty()) {
        echo "  ❌ NO ELECTRICAL HEADS FOUND\n";
    } else {
        foreach ($heads as $head) {
            $count = DB::table('upkj_classifications')
                ->where('class', $class)
                ->where('category', 'Electrical')
                ->where('head_code', $head->head_code)
                ->count();
            
            echo "  ✅ Head {$head->head_code} - {$head->head_name}: $count classifications\n";
        }
    }
    
    // Get total Electrical count
    $total = DB::table('upkj_classifications')
        ->where('class', $class)
        ->where('category', 'Electrical')
        ->count();
    
    echo "  TOTAL ELECTRICAL: $total classifications\n\n";
}

// Show reference from Class A
echo "=== REFERENCE: CLASS A ELECTRICAL (Complete List) ===\n";
$classA = DB::table('upkj_classifications')
    ->where('class', 'A')
    ->where('category', 'Electrical')
    ->orderBy('head_code')
    ->orderBy('subhead_code')
    ->orderBy('subhead_letter')
    ->get();

$currentHead = '';
foreach ($classA as $item) {
    if ($item->head_code !== $currentHead) {
        echo "\nHead {$item->head_code} - {$item->head_name}:\n";
        $currentHead = $item->head_code;
    }
    $code = $item->subhead_code . ($item->subhead_letter ?? '') . ($item->subhead_roman ?? '');
    echo "  $code - {$item->description}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
