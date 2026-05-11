<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MECHANICAL CATEGORY VERIFICATION ===\n\n";

// Check Mechanical for all classes
$classes = ['A', 'B', 'C', 'D', 'E', 'F'];

foreach ($classes as $class) {
    echo "CLASS $class:\n";
    
    // Get all Mechanical heads for this class
    $heads = DB::table('upkj_classifications')
        ->where('class', $class)
        ->where('category', 'Mechanical')
        ->select('head_code', 'head_name')
        ->distinct()
        ->orderBy('head_code')
        ->get();
    
    if ($heads->isEmpty()) {
        echo "  ❌ NO MECHANICAL HEADS FOUND\n";
    } else {
        foreach ($heads as $head) {
            $count = DB::table('upkj_classifications')
                ->where('class', $class)
                ->where('category', 'Mechanical')
                ->where('head_code', $head->head_code)
                ->count();
            
            echo "  ✅ Head {$head->head_code} - {$head->head_name}: $count classifications\n";
        }
    }
    
    // Get total Mechanical count
    $total = DB::table('upkj_classifications')
        ->where('class', $class)
        ->where('category', 'Mechanical')
        ->count();
    
    echo "  TOTAL MECHANICAL: $total classifications\n\n";
}

// Show reference from Class A
echo "=== REFERENCE: CLASS A MECHANICAL (Complete List) ===\n";
$classA = DB::table('upkj_classifications')
    ->where('class', 'A')
    ->where('category', 'Mechanical')
    ->orderBy('head_code')
    ->orderBy('subhead_code')
    ->orderBy('subhead_letter')
    ->get();

if ($classA->isEmpty()) {
    echo "No Mechanical classifications found for Class A\n";
} else {
    $currentHead = '';
    foreach ($classA as $item) {
        if ($item->head_code !== $currentHead) {
            echo "\nHead {$item->head_code} - {$item->head_name}:\n";
            $currentHead = $item->head_code;
        }
        $code = $item->subhead_code . ($item->subhead_letter ?? '') . ($item->subhead_roman ?? '');
        echo "  $code - {$item->description}\n";
    }
}

echo "\n=== VERIFICATION COMPLETE ===\n";
