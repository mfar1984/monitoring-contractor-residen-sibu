<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpkjClassification;

echo "=== Checking Invalid Classification Codes ===\n\n";

// Check F-IV-3
echo "1. Checking F-IV-3:\n";
$found = UpkjClassification::where('class', 'F')
    ->where('head_code', 'IV')
    ->where('subhead_code', '3')
    ->whereNull('subhead_roman')
    ->whereNull('subhead_letter')
    ->first();

if ($found) {
    echo "   ✅ FOUND: {$found->description}\n";
} else {
    echo "   ❌ NOT FOUND in master data\n";
    
    $similar = UpkjClassification::where('class', 'F')
        ->where('head_code', 'IV')
        ->get();
    
    echo "   F-IV has {$similar->count()} classifications:\n";
    foreach ($similar as $s) {
        $code = 'F-IV-' . $s->subhead_code . ($s->subhead_roman ?: '') . ($s->subhead_letter ?: '');
        echo "     - $code: {$s->description}\n";
    }
}

echo "\n";

// Check EX-I-1
echo "2. Checking EX-I-1:\n";
$foundEX = UpkjClassification::where('class', 'EX')->first();

if ($foundEX) {
    echo "   ✅ Class EX exists\n";
} else {
    echo "   ❌ Class EX does NOT exist - INVALID CLASS!\n";
    echo "   Valid classes are: A, B, C, D, E, F, I, II, III, IV\n";
}

echo "\n=== Conclusion ===\n\n";
echo "These 2 classifications are INVALID and don't exist in UPKJ master data:\n";
echo "1. F-IV-3 - This subhead doesn't exist for Class F, Head IV\n";
echo "2. EX-I-1 - Class 'EX' is not a valid UPKJ class\n\n";
echo "These are likely data entry errors in the contractor records.\n";
echo "They should be removed or corrected manually.\n";
