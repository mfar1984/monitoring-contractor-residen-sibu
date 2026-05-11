<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL CATEGORIES IN DATABASE ===\n\n";

// Get all unique categories
$categories = DB::table('upkj_classifications')
    ->select('category')
    ->distinct()
    ->orderBy('category')
    ->pluck('category');

echo "Categories found:\n";
foreach ($categories as $category) {
    $count = DB::table('upkj_classifications')
        ->where('category', $category)
        ->count();
    echo "  - $category: $count classifications\n";
}

echo "\n=== BREAKDOWN BY CLASS ===\n\n";

$classes = ['A', 'B', 'C', 'D', 'E', 'F'];
foreach ($classes as $class) {
    echo "CLASS $class:\n";
    $classCats = DB::table('upkj_classifications')
        ->where('class', $class)
        ->select('category', DB::raw('count(*) as count'))
        ->groupBy('category')
        ->orderBy('category')
        ->get();
    
    foreach ($classCats as $cat) {
        echo "  {$cat->category}: {$cat->count} classifications\n";
    }
    echo "\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
