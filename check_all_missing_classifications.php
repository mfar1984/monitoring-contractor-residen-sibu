<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking ALL Missing Classifications ===\n\n";

// Define all classes
$classes = ['A', 'B', 'C', 'D', 'E', 'F'];

// Define all heads based on screenshots
$heads = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];

$classDescriptions = [
    'A' => 'Works - Above 10,000,000',
    'B' => 'Works - Above 3,000,000 To 10,000,000',
    'C' => 'Works - Above 1,000,000 To 3,000,000',
    'D' => 'Works - Above 1,000,000 To 3,000,000',
    'E' => 'Works - Above 200,000 To 1,000,000',
    'F' => 'Works - 200,000 And Below',
];

echo "Checking which Heads exist for each Class:\n\n";

$missingHeads = [];

foreach ($classes as $class) {
    echo "Class $class ({$classDescriptions[$class]}):\n";
    
    foreach ($heads as $head) {
        $count = UpkjClassification::where('class', $class)
            ->where('head_code', $head)
            ->count();
        
        if ($count > 0) {
            echo "  ✅ Head $head: $count classifications\n";
        } else {
            echo "  ❌ Head $head: MISSING (0 classifications)\n";
            $missingHeads[] = ['class' => $class, 'head' => $head];
        }
    }
    echo "\n";
}

echo "\n=== Summary of Missing Heads ===\n\n";

if (count($missingHeads) > 0) {
    echo "Total missing: " . count($missingHeads) . " Class-Head combinations\n\n";
    
    foreach ($missingHeads as $missing) {
        echo "  - {$missing['class']}-{$missing['head']}\n";
    }
    
    echo "\n\n=== Finding Reference Data ===\n\n";
    echo "For each missing Class-Head, we need to find a reference Class that HAS that Head.\n\n";
    
    foreach ($missingHeads as $missing) {
        $class = $missing['class'];
        $head = $missing['head'];
        
        echo "Missing: $class-$head\n";
        
        // Find which classes have this head
        $classesWithHead = [];
        foreach ($classes as $refClass) {
            $count = UpkjClassification::where('class', $refClass)
                ->where('head_code', $head)
                ->count();
            
            if ($count > 0) {
                $classesWithHead[] = $refClass;
            }
        }
        
        if (count($classesWithHead) > 0) {
            echo "  Reference classes with Head $head: " . implode(', ', $classesWithHead) . "\n";
            echo "  Will copy from: {$classesWithHead[0]}-$head\n";
        } else {
            echo "  ⚠️ WARNING: No reference class found for Head $head!\n";
        }
        echo "\n";
    }
} else {
    echo "✅ No missing Class-Head combinations! All complete.\n";
}
