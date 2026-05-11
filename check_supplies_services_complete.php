<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check what Supplies & Services Heads exist for each class
$classes = ['D', 'E', 'F'];
$heads = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI'];

echo "=== SUPPLIES & SERVICES VERIFICATION ===\n\n";

foreach ($classes as $class) {
    echo "CLASS $class:\n";
    foreach ($heads as $head) {
        $count = DB::table('upkj_classifications')
            ->where('class', $class)
            ->where('head_code', $head)
            ->count();
        
        if ($count > 0) {
            echo "  ✅ Head $head: $count classifications\n";
        } else {
            echo "  ❌ Head $head: MISSING\n";
        }
    }
    echo "\n";
}

// Get total counts
echo "=== TOTAL COUNTS ===\n";
foreach ($classes as $class) {
    $total = DB::table('upkj_classifications')
        ->where('class', $class)
        ->count();
    echo "Class $class: $total total classifications\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
