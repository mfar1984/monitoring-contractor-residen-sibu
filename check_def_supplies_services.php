<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking Supplies & Services for Classes D, E, F ===\n\n";

// Get all unique Heads for Supplies & Services category
$suppliesHeads = UpkjClassification::where('category', 'Supplies & Services')
    ->distinct()
    ->pluck('head_code')
    ->sort()
    ->values();

$classes = ['D', 'E', 'F'];

$missingCombinations = [];

foreach ($suppliesHeads as $head) {
    // Get head name
    $sample = UpkjClassification::where('category', 'Supplies & Services')
        ->where('head_code', $head)
        ->first();
    
    $headName = $sample ? $sample->head_name : 'Unknown';
    
    echo "Head $head: $headName\n";
    
    foreach ($classes as $class) {
        $count = UpkjClassification::where('category', 'Supplies & Services')
            ->where('class', $class)
            ->where('head_code', $head)
            ->count();
        
        if ($count > 0) {
            echo "  ✅ Class $class: $count classifications\n";
        } else {
            echo "  ❌ Class $class: MISSING\n";
            $missingCombinations[] = [
                'class' => $class,
                'head' => $head,
                'category' => 'Supplies & Services',
                'head_name' => $headName
            ];
        }
    }
    echo "\n";
}

echo "\n=== Checking Mechanical for Classes D, E, F ===\n\n";

$mechanicalHeads = UpkjClassification::where('category', 'Mechanical')
    ->distinct()
    ->pluck('head_code')
    ->sort()
    ->values();

foreach ($mechanicalHeads as $head) {
    $sample = UpkjClassification::where('category', 'Mechanical')
        ->where('head_code', $head)
        ->first();
    
    $headName = $sample ? $sample->head_name : 'Unknown';
    
    echo "Head $head: $headName\n";
    
    foreach ($classes as $class) {
        $count = UpkjClassification::where('category', 'Mechanical')
            ->where('class', $class)
            ->where('head_code', $head)
            ->count();
        
        if ($count > 0) {
            echo "  ✅ Class $class: $count classifications\n";
        } else {
            echo "  ❌ Class $class: MISSING\n";
            $missingCombinations[] = [
                'class' => $class,
                'head' => $head,
                'category' => 'Mechanical',
                'head_name' => $headName
            ];
        }
    }
    echo "\n";
}

if (count($missingCombinations) > 0) {
    echo "\n=== SUMMARY: Missing Classifications ===\n\n";
    echo "Total missing: " . count($missingCombinations) . " combinations\n\n";
    
    // Group by class
    $byClass = [];
    foreach ($missingCombinations as $missing) {
        $class = $missing['class'];
        if (!isset($byClass[$class])) {
            $byClass[$class] = [];
        }
        $byClass[$class][] = $missing;
    }
    
    foreach ($byClass as $class => $items) {
        echo "Class $class: " . count($items) . " missing\n";
        foreach ($items as $item) {
            echo "  - {$item['head']} ({$item['category']}): {$item['head_name']}\n";
        }
        echo "\n";
    }
    
    echo "These should be added to complete the master data for Classes D, E, F.\n";
} else {
    echo "\n✅ All Supplies & Services and Mechanical classifications are complete for Classes D, E, F!\n";
}
