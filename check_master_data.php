<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking UPKJ Classifications master data...\n\n";

// Get total classifications in master data
$totalMaster = App\Models\UpkjClassification::count();
echo "Total classifications in master data: {$totalMaster}\n\n";

// Get sample of missing classifications
$missingCodes = [
    'E-I-2iia',
    'E-I-2iib', 
    'E-I-2iic',
    'E-I-3iia',
    'F-I-2ia',
    'F-I-2iia',
    'F-I-2ib',
    'F-I-2iib',
    'F-I-2iic',
    'F-I-3ia',
    'F-I-3iia',
    'E-VI-5ia',
    'E-VI-5iia',
    'D-VI-5ia',
    'D-VI-5iia'
];

echo "Checking if these codes exist in master data:\n\n";

foreach ($missingCodes as $code) {
    $parts = explode('-', $code);
    $class = $parts[0];
    $head = $parts[1];
    
    // Parse subhead
    preg_match('/^(\d+)([a-z]*)([ivx]*)$/i', $parts[2], $matches);
    $subheadCode = $matches[1] ?? '';
    $subheadLetter = $matches[2] ?? null;
    $subheadRoman = $matches[3] ?? null;
    
    $found = App\Models\UpkjClassification::where('class', $class)
        ->where('head_code', $head)
        ->where('subhead_code', $subheadCode)
        ->where(function($q) use ($subheadLetter) {
            if ($subheadLetter) {
                $q->where('subhead_letter', $subheadLetter);
            } else {
                $q->whereNull('subhead_letter');
            }
        })
        ->where(function($q) use ($subheadRoman) {
            if ($subheadRoman) {
                $q->where('subhead_roman', $subheadRoman);
            } else {
                $q->whereNull('subhead_roman');
            }
        })
        ->first();
    
    if ($found) {
        echo "✅ {$code} - FOUND\n";
    } else {
        echo "❌ {$code} - NOT FOUND\n";
    }
}

// Check what classes exist
echo "\n\nClasses in master data:\n";
$classes = App\Models\UpkjClassification::select('class')->distinct()->orderBy('class')->pluck('class');
foreach ($classes as $class) {
    $count = App\Models\UpkjClassification::where('class', $class)->count();
    echo "  - Class {$class}: {$count} classifications\n";
}
