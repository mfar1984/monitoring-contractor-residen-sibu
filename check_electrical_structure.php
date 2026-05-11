<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ELECTRICAL CLASSIFICATIONS STRUCTURE ===\n\n";

// Get all Electrical records
$electrical = DB::table('upkj_classifications')
    ->where('category', 'Electrical')
    ->orderBy('class')
    ->orderBy('head_code')
    ->orderBy('subhead_code')
    ->orderBy('subhead_letter')
    ->get();

echo "Total Electrical classifications: " . $electrical->count() . "\n\n";

// Group by class
$byClass = $electrical->groupBy('class');

foreach ($byClass as $class => $items) {
    echo "CLASS: $class\n";
    echo "Class Description: " . $items->first()->class_description . "\n";
    echo "Total: " . $items->count() . " classifications\n";
    
    // Group by head
    $byHead = $items->groupBy('head_code');
    foreach ($byHead as $headCode => $headItems) {
        $headName = $headItems->first()->head_name;
        echo "  Head $headCode - $headName: " . $headItems->count() . " classifications\n";
    }
    echo "\n";
}

echo "=== DETAILED LIST ===\n\n";

$currentClass = '';
$currentHead = '';
foreach ($electrical as $item) {
    if ($item->class !== $currentClass) {
        echo "\n========================================\n";
        echo "CLASS {$item->class}: {$item->class_description}\n";
        echo "========================================\n";
        $currentClass = $item->class;
        $currentHead = '';
    }
    
    if ($item->head_code !== $currentHead) {
        echo "\nHead {$item->head_code} - {$item->head_name}:\n";
        $currentHead = $item->head_code;
    }
    
    $code = $item->subhead_code . ($item->subhead_letter ?? '') . ($item->subhead_roman ?? '');
    echo "  $code - {$item->description}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
